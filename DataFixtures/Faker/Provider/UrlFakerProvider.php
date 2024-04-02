<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Xm\SymfonyBundle\Model\Date;
use Xm\SymfonyBundle\Model\Url;

/**
 * @codeCoverageIgnore
 */
class UrlFakerProvider extends Faker\Provider\Base
{
    public function urlVo(): Url {
        $faker = Faker\Factory::create('en_CA');

        return Url::fromString($faker->url());
    }
}
