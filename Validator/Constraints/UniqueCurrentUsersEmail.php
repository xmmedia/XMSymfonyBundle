<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueCurrentUsersEmail extends Constraint
{
    public $message = 'This email address has already been used.';
}
