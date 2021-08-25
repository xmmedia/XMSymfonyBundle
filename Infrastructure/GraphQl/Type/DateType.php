<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Xm\SymfonyBundle\Util\StringUtil;

final class DateType extends ScalarType implements AliasedInterface
{
    private const NAME = 'Date';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'Date represented as string, the format of Y-m-d.',
        ]);
    }

    /**
     * @param \DateTimeInterface $value
     */
    public function serialize($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\is_string($value)) {
            return $value;
        }

        return $value->format('Y-m-d');
    }

    /**
     * @param string $value
     */
    public function parseValue($value): ?\DateTimeImmutable
    {
        if (empty(StringUtil::trim($value))) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }

    /**
     * @param StringValueNode $valueNode
     */
    public function parseLiteral($valueNode, array $variables = null): ?\DateTimeImmutable
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
