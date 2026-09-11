<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Service;

use Carbon\CarbonImmutable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenancePage;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenancePageTest extends BaseTestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function testRender(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-10 12:00', 'America/Edmonton'));
        $message = $this->faker()->sentence();
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__.'/../../../Resources/views', 'XmSymfony');

        $page = (new MaintenancePage(new Environment($loader, ['strict_variables' => true]), 'America/Edmonton'))
            ->render(new MaintenanceSettings($message, CarbonImmutable::now()->addMinutes(30)));

        $this->assertStringContainsString('We\'re doing some maintenance', $page);
        $this->assertStringContainsString(htmlspecialchars($message), $page);
        $this->assertStringContainsString('12:30 pm MDT', $page);
    }

    public function testRenderedWithSettings(): void
    {
        $message = $this->faker()->sentence();
        $until = CarbonImmutable::now()->addHour();

        $twig = \Mockery::mock(Environment::class);
        $twig->shouldReceive('render')
            ->once()
            ->withArgs(static function (string $template, array $context) use ($message, $until): bool {
                return MaintenancePage::TEMPLATE === $template
                    && $message === $context['message']
                    && $until->getTimestamp() === $context['until']->getTimestamp()
                    && 'America/Halifax' === $context['until']->getTimezone()->getName();
            })
            ->andReturn('page');

        $page = (new MaintenancePage($twig, 'America/Halifax'))->render(new MaintenanceSettings($message, $until));

        $this->assertSame('page', $page);
    }

    public function testRenderedWithoutUntil(): void
    {
        $twig = \Mockery::mock(Environment::class);
        $twig->shouldReceive('render')
            ->once()
            ->withArgs(static fn (string $template, array $context): bool => null === $context['until'])
            ->andReturn('page');

        $this->assertSame('page', (new MaintenancePage($twig))->render(new MaintenanceSettings()));
    }
}
