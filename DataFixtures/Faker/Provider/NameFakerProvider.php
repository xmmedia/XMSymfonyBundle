<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;

/**
 * @property string $name
 *
 * @codeCoverageIgnore
 */
class NameFakerProvider extends Faker\Provider\Person
{
    public function name($gender = null): string
    {
        return substr(parent::name($gender), 0, 25);
    }
}
