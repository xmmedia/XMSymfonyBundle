<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Carbon\CarbonImmutable;
use JetBrains\PhpStorm\ArrayShape;
use Xm\SymfonyBundle\Model\Email;

/**
 * @phpstan-type EmailSuppressionResult array{
 *     suppressed: bool,
 *     reason: string|null,
 *     reasonHuman: string|null,
 *     dateAdded: CarbonImmutable|null,
 *     espUrl: string|null,
 * }
 */
interface EmailSuppressionCheckerInterface
{
    /**
     * @return EmailSuppressionResult
     */
    #[ArrayShape([
        'suppressed'  => 'bool',
        'reason'      => 'null|string',
        'reasonHuman' => 'null|string',
        'dateAdded'   => CarbonImmutable::class.'|null',
        'espUrl'      => 'null|string',
    ])]
    public function check(Email $email): array;
}
