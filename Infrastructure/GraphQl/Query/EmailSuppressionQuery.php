<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use Carbon\CarbonImmutable;
use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Xm\SymfonyBundle\Infrastructure\Service\EmailSuppressionCheckerInterface;
use Xm\SymfonyBundle\Model\Email;

/**
 * @phpstan-import-type EmailSuppressionResult from EmailSuppressionCheckerInterface
 */
final class EmailSuppressionQuery implements QueryInterface
{
    public function __construct(private readonly EmailSuppressionCheckerInterface $suppressionChecker)
    {
    }

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
    public function __invoke(string $email): array
    {
        return $this->suppressionChecker->check(Email::fromString($email));
    }
}
