<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Command;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Tester\CommandTester;
use Xm\SymfonyBundle\Command\MaintenanceCommand;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenanceCommandTest extends BaseTestCase
{
    private const string TIME_ZONE = 'America/Edmonton';

    private string $dir;
    private MaintenanceMode $maintenanceMode;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/maintenance_'.bin2hex(random_bytes(8));
        $this->maintenanceMode = new MaintenanceMode($this->dir.'/maintenance');
    }

    protected function tearDown(): void
    {
        $this->maintenanceMode->disable();
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
        CarbonImmutable::setTestNow();
        putenv('SSH_CLIENT');

        parent::tearDown();
    }

    public function testStatusWhenOff(): void
    {
        $commandTester = $this->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Maintenance mode is off.', $commandTester->getDisplay());
    }

    public function testOn(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-10 12:00', self::TIME_ZONE));
        $message = $this->faker()->sentence();
        $ip = $this->faker()->ipv4();

        $commandTester = $this->execute([
            'action'     => 'on',
            '--message'  => $message,
            '--until'    => '+30 minutes',
            '--allow-ip' => [$ip, '10.0.0.0/8'],
        ]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Maintenance mode is on.', $commandTester->getDisplay());
        $this->assertStringContainsString('2026-09-10 12:30 pm MDT', $commandTester->getDisplay());

        $settings = $this->maintenanceMode->settings();
        $this->assertSame($message, $settings->message());
        $this->assertEquals(CarbonImmutable::parse('2026-09-10 12:30', self::TIME_ZONE), $settings->until());
        $this->assertSame([$ip, '10.0.0.0/8'], $settings->allowedIps());
    }

    public function testOnWithDefaults(): void
    {
        $commandTester = $this->execute(['action' => 'on']);

        $this->assertStringContainsString('(default)', $commandTester->getDisplay());
        $this->assertEquals(new MaintenanceSettings(), $this->maintenanceMode->settings());
    }

    public function testOnWhenOnUpdatesIt(): void
    {
        $message = $this->faker()->sentence();
        $until = CarbonImmutable::now()->addHour()->startOfSecond();
        $ip = $this->faker()->ipv4();
        $this->maintenanceMode->enable(new MaintenanceSettings($message, $until, [$ip, '10.0.0.1']));

        $commandTester = $this->execute([
            'action'      => 'on',
            '--allow-ip'  => ['10.0.0.2'],
            '--remove-ip' => ['10.0.0.1'],
        ]);

        $this->assertStringContainsString('already on & has been updated', $commandTester->getDisplay());

        $settings = $this->maintenanceMode->settings();
        $this->assertSame($message, $settings->message());
        $this->assertEquals($until, $settings->until());
        $this->assertSame([$ip, '10.0.0.2'], $settings->allowedIps());
    }

    public function testOnClearsWithEmptyValues(): void
    {
        $this->maintenanceMode->enable(new MaintenanceSettings(
            $this->faker()->sentence(),
            CarbonImmutable::now()->addHour(),
            [$this->faker()->ipv4()],
        ));

        $this->execute(['action' => 'on', '--message' => '', '--until' => '', '--reset-ips' => true]);

        $this->assertEquals(new MaintenanceSettings(), $this->maintenanceMode->settings());
    }

    public function testResetIpsBeforeAdding(): void
    {
        $ip = $this->faker()->ipv4();
        $this->maintenanceMode->enable(new MaintenanceSettings(null, null, ['10.0.0.1']));

        $this->execute(['action' => 'on', '--reset-ips' => true, '--allow-ip' => [$ip]]);

        $this->assertSame([$ip], $this->maintenanceMode->settings()->allowedIps());
    }

    public function testAllowMyIp(): void
    {
        $ip = $this->faker()->ipv4();
        putenv('SSH_CLIENT='.$ip.' 51234 22');

        $this->execute(['action' => 'on', '--allow-my-ip' => true]);

        $this->assertSame([$ip], $this->maintenanceMode->settings()->allowedIps());
    }

    public function testAllowMyIpWithoutSsh(): void
    {
        putenv('SSH_CLIENT');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('SSH_CLIENT');

        $this->execute(['action' => 'on', '--allow-my-ip' => true]);
    }

    #[DataProvider('invalidIps')]
    public function testInvalidIp(string $ip): void
    {
        try {
            $this->execute(['action' => 'on', '--allow-ip' => [$ip]]);
            $this->fail('An invalid IP was accepted.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('is not an IP or CIDR range', $e->getMessage());
        }

        $this->assertFalse($this->maintenanceMode->isOn());
    }

    public static function invalidIps(): \Generator
    {
        yield ['not an ip'];
        yield ['10.0.0'];
        yield ['10.0.0.0/33'];
        yield ['10.0.0.0/a'];
        yield ['2001:db8::/129'];
    }

    public function testUntilInThePast(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('is in the past');

        $this->execute(['action' => 'on', '--until' => '-1 hour']);
    }

    public function testUntilUnreadable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('can be read');

        $this->execute(['action' => 'on', '--until' => 'not a time']);
    }

    public function testStatusWhenOn(): void
    {
        $message = $this->faker()->sentence();
        $ip = $this->faker()->ipv4();
        $this->maintenanceMode->enable(new MaintenanceSettings($message, null, [$ip]));

        $display = $this->execute([])->getDisplay();

        $this->assertStringContainsString('Maintenance mode is on.', $display);
        $this->assertStringContainsString($message, $display);
        $this->assertStringContainsString($ip, $display);
    }

    public function testOff(): void
    {
        $this->maintenanceMode->enable(new MaintenanceSettings());

        $commandTester = $this->execute(['action' => 'off']);

        $this->assertStringContainsString('Maintenance mode is off.', $commandTester->getDisplay());
        $this->assertFalse($this->maintenanceMode->isOn());
    }

    public function testOffWhenOff(): void
    {
        $commandTester = $this->execute(['action' => 'off']);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('already off', $commandTester->getDisplay());
    }

    public function testOffWithOnOption(): void
    {
        $this->maintenanceMode->enable(new MaintenanceSettings());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('only applies to "on"');

        $this->execute(['action' => 'off', '--message' => $this->faker()->sentence()]);
    }

    public function testInvalidAction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->execute(['action' => 'toggle']);
    }

    private function execute(array $input): CommandTester
    {
        $commandTester = new CommandTester(new MaintenanceCommand($this->maintenanceMode, self::TIME_ZONE));
        $commandTester->execute($input);

        return $commandTester;
    }
}
