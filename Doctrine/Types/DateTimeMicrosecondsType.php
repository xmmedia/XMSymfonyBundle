<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps a SQL DATETIME(6) (datetime with microseconds) to a PHP DateTime object.
 */
class DateTimeMicrosecondsType extends Type
{
    public const TYPENAME = 'datetime_microseconds';
    private const FORMAT = 'Y-m-d H:i:s.u';

    public function getName(): string
    {
        return self::TYPENAME;
    }

    public function getSQLDeclaration(
        array $column,
        AbstractPlatform $platform,
    ): string {
        return $platform->getDateTimeTypeDeclarationSQL($column).'(6)';
    }

    public function convertToDatabaseValue(
        $value,
        AbstractPlatform $platform,
    ): ?string {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(self::FORMAT);
        }

        // DBAL 4 moved the named constructors onto dedicated exception classes
        if (class_exists(InvalidType::class)) {
            throw InvalidType::new($value, self::TYPENAME, ['null', \DateTimeInterface::class]);
        }

        // @phpstan-ignore staticMethod.notFound (DBAL 3 only)
        throw ConversionException::conversionFailedInvalidType(
            $value,
            self::TYPENAME,
            ['null', \DateTimeInterface::class],
        );
    }

    public function convertToPHPValue(
        $value,
        AbstractPlatform $platform,
    ): ?\DateTimeInterface {
        if (null === $value || $value instanceof \DateTimeInterface) {
            return $value;
        }

        $val = \DateTimeImmutable::createFromFormat(self::FORMAT, $value);

        if (!$val) {
            $val = date_create($value);
        }

        if (!$val) {
            // DBAL 4 moved the named constructors onto dedicated exception classes
            if (class_exists(InvalidFormat::class)) {
                throw InvalidFormat::new($value, self::TYPENAME, self::FORMAT);
            }

            // @phpstan-ignore staticMethod.notFound (DBAL 3 only)
            throw ConversionException::conversionFailedFormat(
                $value,
                self::TYPENAME,
                self::FORMAT,
            );
        }

        return $val;
    }
}
