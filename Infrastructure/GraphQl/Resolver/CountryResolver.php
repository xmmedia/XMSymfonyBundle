<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Xm\SymfonyBundle\DataProvider\CountryProvider;
use Xm\SymfonyBundle\Model\Country;

/**
 * @deprecated use CountryQuery instead with overblog/graphql-bundle ^0.14
 */
class CountryResolver implements ResolverInterface
{
    /**
     * @return Country[]
     */
    public function __invoke(): array
    {
        return array_values(array_map(function (string $country) {
            return Country::fromString($country);
        }, CountryProvider::all()));
    }
}
