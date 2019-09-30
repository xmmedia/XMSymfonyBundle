<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

/**
 * @method static Gender FEMALE();
 * @method static Gender MALE();
 */
class Gender extends Enum
{
    public const FEMALE = 'FEMALE';
    public const MALE = 'MALE';
}
