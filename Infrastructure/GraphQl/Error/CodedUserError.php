<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Error;

use GraphQL\Error\ProvidesExtensions;
use Overblog\GraphQLBundle\Error\UserError;

/**
 * A UserError sent with its code in extensions.code (Apollo style), for the frontend to check.
 */
abstract class CodedUserError extends UserError implements ProvidesExtensions
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    abstract public function errorCode(): string;

    public function getExtensions(): array
    {
        return ['code' => $this->errorCode()];
    }
}
