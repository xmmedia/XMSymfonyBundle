<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Service;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenanceSettingsTest extends BaseTestCase
{
    public function testJsonRoundTrip(): void
    {
        $settings = new MaintenanceSettings(
            $this->faker()->sentence(),
            CarbonImmutable::instance($this->faker()->dateTimeBetween('+1 hour', '+1 day'))->startOfSecond(),
            [$this->faker()->ipv4(), $this->faker()->ipv6()],
            MaintenanceSettings::generateKey(),
        );

        $result = MaintenanceSettings::fromJson(json_encode($settings, \JSON_THROW_ON_ERROR));

        $this->assertSame($settings->message(), $result->message());
        $this->assertEquals($settings->until(), $result->until());
        $this->assertSame($settings->allowedIps(), $result->allowedIps());
        $this->assertSame($settings->key(), $result->key());
    }

    #[DataProvider('unreadableJson')]
    public function testFromUnreadableJsonUsesDefaults(string $json): void
    {
        $settings = MaintenanceSettings::fromJson($json);

        $this->assertNull($settings->message());
        $this->assertNull($settings->until());
        $this->assertSame([], $settings->allowedIps());
        $this->assertNull($settings->key());
        $this->assertSame(MaintenanceSettings::DEFAULT_MESSAGE, $settings->displayMessage());
    }

    public static function unreadableJson(): \Generator
    {
        yield 'empty' => [''];
        yield 'not json' => ['on'];
        yield 'not an object' => ['"on"'];
        yield 'wrong types' => ['{"message": 1, "until": "not a date", "allowedIps": "10.0.0.1", "key": 1}'];
        yield 'empty key' => ['{"key": ""}'];
        yield 'blank message' => ['{"message": "  "}'];
    }

    public function testFromJsonSkipsIpsThatArentStrings(): void
    {
        $ip = $this->faker()->ipv4();

        $settings = MaintenanceSettings::fromJson(json_encode(['allowedIps' => [1, $ip, null]], \JSON_THROW_ON_ERROR));

        $this->assertSame([$ip], $settings->allowedIps());
    }

    public function testWithMessage(): void
    {
        $message = $this->faker()->sentence();

        $settings = (new MaintenanceSettings())->withMessage('  '.$message.' ');

        $this->assertSame($message, $settings->message());
        $this->assertSame($message, $settings->displayMessage());
        $this->assertNull($settings->withMessage('')->message());
        $this->assertNull($settings->withMessage(null)->message());
    }

    public function testWithAllowedIpsRemovesDuplicates(): void
    {
        $ip = $this->faker()->ipv4();

        $settings = (new MaintenanceSettings())->withAllowedIps([$ip, $ip]);

        $this->assertSame([$ip], $settings->allowedIps());
    }

    public function testWithKeepsTheOtherSettings(): void
    {
        $message = $this->faker()->sentence();
        $until = CarbonImmutable::now()->addHour();
        $ips = [$this->faker()->ipv4()];
        $key = MaintenanceSettings::generateKey();

        $settings = (new MaintenanceSettings($message, $until, $ips, $key))
            ->withUntil(null)
            ->withUntil($until)
            ->withMessage($message)
            ->withAllowedIps($ips);

        $this->assertSame($message, $settings->message());
        $this->assertSame($until, $settings->until());
        $this->assertSame($ips, $settings->allowedIps());
        $this->assertSame($key, $settings->key());
    }

    public function testAllowsKey(): void
    {
        $key = MaintenanceSettings::generateKey();
        $settings = (new MaintenanceSettings())->withKey($key);

        $this->assertTrue($settings->allowsKey($key));
        $this->assertFalse($settings->allowsKey(MaintenanceSettings::generateKey()));
        $this->assertFalse($settings->allowsKey(null));
        $this->assertFalse((new MaintenanceSettings())->allowsKey(''));
    }

    public function testGenerateKey(): void
    {
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', MaintenanceSettings::generateKey());
        $this->assertNotSame(MaintenanceSettings::generateKey(), MaintenanceSettings::generateKey());
    }

    public function testAllows(): void
    {
        $settings = new MaintenanceSettings(null, null, ['10.0.0.0/8', '192.168.1.5', '2001:db8::/32']);

        $this->assertTrue($settings->allows('10.1.2.3'));
        $this->assertTrue($settings->allows('192.168.1.5'));
        $this->assertTrue($settings->allows('2001:db8::1'));
        $this->assertFalse($settings->allows('192.168.1.6'));
        $this->assertFalse($settings->allows(null));
    }

    public function testAllowsNoOneByDefault(): void
    {
        $this->assertFalse((new MaintenanceSettings())->allows($this->faker()->ipv4()));
    }

    public function testRetryAfter(): void
    {
        $now = CarbonImmutable::now();

        $this->assertSame(900, (new MaintenanceSettings(null, $now->addMinutes(15)))->retryAfter($now));
        $this->assertSame(300, (new MaintenanceSettings(null, $now->subMinute()))->retryAfter($now));
        $this->assertSame(300, (new MaintenanceSettings())->retryAfter($now));
    }
}
