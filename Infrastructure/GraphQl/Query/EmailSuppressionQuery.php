<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Xm\SymfonyBundle\Infrastructure\Service\EmailSuppressionCheckerInterface;
use Xm\SymfonyBundle\Model\Email;

final class EmailSuppressionQuery implements QueryInterface
{
    public function __construct(private EmailSuppressionCheckerInterface $suppressionChecker)
    {
    }

    #[ArrayShape(['suppressed' => 'bool', 'reason' => 'null|string', 'dateAdded' => 'null|string', 'postmarkUrl' => 'string'])]
    public function __invoke(string $email): array
    {
        return $this->suppressionChecker->check(Email::fromString($email));
    }
}
