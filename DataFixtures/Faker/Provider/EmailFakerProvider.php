<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Xm\SymfonyBundle\Model\Email;

/**
 * @codeCoverageIgnore
 */
class EmailFakerProvider extends Faker\Provider\Internet
{
    public function emailVo(): Email
    {
        return Email::fromString(static::email());
    }
}
