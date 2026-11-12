<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker\Provider\Base;
use Faker\Provider\Internet as FakerInternet;

/**
 * @codeCoverageIgnore
 */
class InternetFakerProvider extends Base
{
    /**
     * Longer than Faker's default of 6-20 characters,
     * so the passwords meet a typical minimum length.
     */
    public function password($minLength = 12, $maxLength = 250): string
    {
        return (new FakerInternet($this->generator))->password($minLength, $maxLength);
    }
}
