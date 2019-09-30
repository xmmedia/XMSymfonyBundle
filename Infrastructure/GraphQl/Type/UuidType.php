<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
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
     * @param UuidInterface|string $value strings must be valid UUIDs
     */
    public function serialize($value): ?string
    {
        if ($value instanceof UuidInterface) {
            return $value->toString();
        }

        return \is_string($value) && Uuid::isValid($value) ? $value : null;
    }

    /**
     * @param string|mixed $value
     */
    public function parseValue($value): ?string
    {
        return \is_string($value) && Uuid::isValid($value) ? $value : null;
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
