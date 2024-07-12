<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Utils\Utils;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Model\UuidInterface;

trait UuidTypeTrait
{
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
     * @param StringValueNode $valueNode
     */
    public function parseLiteral($valueNode, ?array $variables = null): ?string
    {
        if (!$valueNode instanceof StringValueNode) {
            return null;
        }

        return $this->parseValue($valueNode->value);
    }
}
