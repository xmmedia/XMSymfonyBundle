<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use Xm\SymfonyBundle\Model\Enum;

/**
 * @method static TestEnum FEMALE()
 * @method static TestEnum MALE()
 */
final class TestEnum extends Enum
{
    public const FEMALE = 'FEMALE';
    public const MALE = 'MALE';
}
