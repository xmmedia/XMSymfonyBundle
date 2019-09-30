<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Xm\SymfonyBundle\DataProvider\ProvinceProvider;
use Xm\SymfonyBundle\Model\Province;

class ProvinceResolver implements ResolverInterface
{
    /**
     * @return Province[]
     */
    public function __invoke(): array
    {
        return array_values(array_map(function (string $province) {
            return Province::fromString($province);
        }, ProvinceProvider::all(false)));
    }
}
