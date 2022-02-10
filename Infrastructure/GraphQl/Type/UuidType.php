<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Model\UuidInterface;

final class UuidType extends ScalarType implements AliasedInterface
{
    private const NAME = 'UUID';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'A UUID represented as string.',
        ]);
    }

    /**
     * @param UuidInterface|\Ramsey\Uuid\UuidInterface|string $value Strings must be valid UUIDs
     */
    public function serialize($value): string
    {
        if ($value instanceof UuidInterface || $value instanceof \Ramsey\Uuid\UuidInterface) {
            return $value->toString();
        }

        if (\is_string($value) && Uuid::isValid($value)) {
            return $value;
        }

        throw new Error('Cannot serialize value as UUID: '.Utils::printSafe($value));
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

    /**
     * @param StringValueNode $valueNode
     */
    public function parseLiteral($valueNode, array $variables = null): ?string
    {
        if (!$valueNode instanceof StringValueNode) {
            return null;
        }

        return $this->parseValue($valueNode->value);
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
