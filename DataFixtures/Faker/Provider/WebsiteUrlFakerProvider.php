<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Xm\SymfonyBundle\Model\Date;
use Xm\SymfonyBundle\Model\WebsiteUrl;

/**
 * @codeCoverageIgnore
 */
class WebsiteUrlFakerProvider extends Faker\Provider\Base
{
    public function websiteUrlVo(): WebsiteUrl {
        $faker = Faker\Factory::create('en_CA');

        return WebsiteUrl::fromString($faker->url());
    }
}
