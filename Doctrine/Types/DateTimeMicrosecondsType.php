<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps a SQL DATETIME(6) (datetime with microseconds) to a PHP DateTime object.
 */
class DateTimeMicrosecondsType extends Type
{
    public const TYPENAME = 'datetime_microseconds';
    private const FORMAT = 'Y-m-d H:i:s.u';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::TYPENAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(
        array $column,
        AbstractPlatform $platform
    ): string {
        return $platform->getDateTimeTypeDeclarationSQL($column).'(6)';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue(
        $value,
        AbstractPlatform $platform
    ): ?string {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(self::FORMAT);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue(
        $value,
        AbstractPlatform $platform
    ): ?\DateTimeInterface {
        if (null === $value || $value instanceof \DateTimeInterface) {
            return $value;
        }

        $val = \DateTimeImmutable::createFromFormat(self::FORMAT, $value);

        if (!$val) {
            $val = date_create($value);
        }

        if (!$val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), self::FORMAT);
        }

        return $val;
    }
}
