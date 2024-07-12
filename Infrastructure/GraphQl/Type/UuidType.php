<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Model\UuidInterface;

final class UuidType extends ScalarType implements AliasedInterface
{
    use UuidTypeTrait;

    private const NAME = 'UUID';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'A UUID represented as string.',
        ]);
    }

    /**
     * @param string|mixed $value
     */
    public function parseValue($value): string
    {
        if (\is_string($value) && Uuid::isValid($value)) {
            return $value;
        }

        if ($value instanceof UuidInterface && Uuid::isValid((string) $value)) {
            return (string) $value;
        }

        throw new Error('Cannot represent value as UUID: '.Utils::printSafe($value));
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
