<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

enum Gender: string implements ValueObjectEnum
{
    use ValueObjectEnumTrait;

    case FEMALE = 'FEMALE';
    case MALE = 'MALE';
}
