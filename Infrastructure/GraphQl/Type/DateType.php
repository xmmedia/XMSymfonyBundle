<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Error\UserError;

final class DateType extends ScalarType implements AliasedInterface
{
    private const NAME = 'Date';
    private const FORMAT = 'Y-m-d';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'Date represented as string, the format of '.self::FORMAT.'.',
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

        return $value->format(self::FORMAT);
    }

    /**
     * @param string $value
     */
    public function parseValue($value): ?\DateTimeImmutable
    {
        if (null === empty($value)) {
            return null;
        }

        if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $value)) {
            throw new UserError('The date is not in the format of YYYY-MM-DD. Received: '.$value);
        }

        $date = \DateTimeImmutable::createFromFormat(self::FORMAT, $value);

        if (!$date) {
            throw new UserError('Unable to parse Date. Ensure format is '.self::FORMAT.'.');
        }

        return $date;
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
