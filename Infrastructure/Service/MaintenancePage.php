<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Carbon\CarbonImmutable;
use Twig\Environment;

/**
 * Renders the maintenance page (@XmSymfony/maintenance.html.twig, overridable in
 * templates/bundles/XmSymfonyBundle/). app:maintenance renders it to a file when it's turned on.
 */
final readonly class MaintenancePage
{
    public const string TEMPLATE = '@XmSymfony/maintenance.html.twig';

    public function __construct(
        private Environment $twig,
        private ?string $timeZone = null,
    ) {
    }

    public function render(MaintenanceSettings $settings): string
    {
        $until = null;
        if (null !== $settings->until()) {
            $until = CarbonImmutable::instance($settings->until())
                ->setTimezone($this->timeZone ?? date_default_timezone_get());
        }

        return $this->twig->render(self::TEMPLATE, [
            'message' => $settings->displayMessage(),
            'until'   => $until,
        ]);
    }
}
