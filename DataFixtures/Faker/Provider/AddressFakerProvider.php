<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Faker\Provider\en_CA\Address as FakerAddress;
use Xm\SymfonyBundle\Model\Address;

/**
 * @property Address $addressVo
 * @property array   $addressArray
 *
 * @codeCoverageIgnore
 */
class AddressFakerProvider extends FakerAddress
{
    public function addressVo(): Address
    {
        return Address::fromArray($this->addressArray());
    }

    public function addressArray(): array
    {
        $faker = Faker\Factory::create('en_CA');

        return [
            'line1'      => $faker->streetAddress,
            'line2'      => $faker->streetAddress,
            'city'       => $faker->city,
            'province'   => $faker->provinceAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ];
    }
}
