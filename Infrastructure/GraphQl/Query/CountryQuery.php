<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Xm\SymfonyBundle\DataProvider\CountryProvider;
use Xm\SymfonyBundle\Model\Country;

class CountryQuery implements QueryInterface
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
