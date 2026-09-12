<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\Printer;
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

    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
        if (!$valueNode instanceof StringValueNode) {
            throw new Error('Cannot represent a non-string value as UUID: '.Printer::doPrint($valueNode), $valueNode);
        }

        return $this->parseValue($valueNode->value);
    }
}
