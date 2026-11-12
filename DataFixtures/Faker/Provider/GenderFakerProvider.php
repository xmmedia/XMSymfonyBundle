<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Xm\SymfonyBundle\Model\Gender;

/**
 * @codeCoverageIgnore
 */
class GenderFakerProvider extends Faker\Provider\Base
{
    public function gender(): string
    {
        return static::randomElement(Gender::getValues());
    }
}
