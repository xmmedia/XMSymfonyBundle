<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Carbon\CarbonImmutable;
use JetBrains\PhpStorm\ArrayShape;
use Xm\SymfonyBundle\Model\Email;

interface EmailSuppressionCheckerInterface
{
    #[ArrayShape([
        'suppressed'  => 'bool',
        'reason'      => 'null|string',
        'reasonHuman' => 'null|string',
        'dateAdded'   => CarbonImmutable::class.'|null',
        'postmarkUrl' => 'null|string',
    ])]
    public function check(Email $email): array;
}
