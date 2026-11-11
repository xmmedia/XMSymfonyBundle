<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Xm\SymfonyBundle\Util\StringUtil;
use Xm\SymfonyBundle\Util\Utils;

final class DateTimeType extends ScalarType implements AliasedInterface
{
    private const string NAME = 'DateTime';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'Date & time represented as string. The serialized date will be in the RFC3339 format and will include the server timezone (UTC). The input should be a string in a format understood by PHP DateTime constructors and containing the user\'s timezone. If the input timezone is not included, UTC will be assumed.',
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

        return $value->format(\DateTimeInterface::RFC3339);
    }

    /**
     * @param string $value
     */
    public function parseValue($value): ?\DateTimeImmutable
    {
        $value = StringUtil::trim($value);

        if (null === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw $this->parseError($value);
        }

        try {
            $dateTime = new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw $this->parseError($value);
        }

        // impossible dates (2023-02-30) are rolled over with only a warning
        if (false !== \DateTimeImmutable::getLastErrors()) {
            throw $this->parseError($value);
        }

        return $dateTime->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * @param StringValueNode $valueNode
     */
    public function parseLiteral($valueNode, ?array $variables = null): ?\DateTimeImmutable
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

    private function parseError(mixed $value): UserError
    {
        return new UserError('Unable to parse DateTime. Received: "'.Utils::printSafe($value).'"');
    }
}
