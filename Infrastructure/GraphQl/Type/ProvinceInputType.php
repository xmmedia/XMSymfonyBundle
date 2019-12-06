<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Xm\SymfonyBundle\DataProvider\ProvinceProvider;
use Xm\SymfonyBundle\Model\Province;

class ProvinceInputType extends EnumType implements AliasedInterface
{
    private const NAME = 'ProvinceInput';

    public function __construct()
    {
        $config = [
            'values'      => ProvinceProvider::abbreviations(false),
            'description' => 'A Canadian province or United States state.',
        ];

        parent::__construct($config);
    }

    /**
     * @param Province $value
     */
    public function serialize($value)
    {
        if ($value instanceof Province) {
            return $value->toString();
        }

        throw new Error('Cannot serialize Province value as enum: '.Utils::printSafe($value));
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
