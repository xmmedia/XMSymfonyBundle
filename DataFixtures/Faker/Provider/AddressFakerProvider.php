<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker\Provider\en_CA\Address as FakerAddress;
use Xm\SymfonyBundle\DataProvider\CountryProvider;
use Xm\SymfonyBundle\Model\Address;
use Xm\SymfonyBundle\Model\Country;
use Xm\SymfonyBundle\Model\Province;

/**
 * @property Address  $addressVo
 * @property array    $addressArray
 * @property Province $province
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
        return [
            'line1'      => parent::streetAddress(),
            'line2'      => parent::streetAddress(),
            'city'       => parent::city(),
            'province'   => parent::provinceAbbr(),
            'postalCode' => parent::postcode(),
            'country'    => 'CA',
        ];
    }

    public function countryVo(): Country
    {
        return Country::fromString(
            parent::randomElement(CountryProvider::abbreviations())
        );
    }

    public function provinceVo(): Province
    {
        return Province::fromString(parent::provinceAbbr());
    }
}
