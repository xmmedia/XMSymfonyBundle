<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Validator\Constraints;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;

class UniqueCurrentUsersEmailValidator extends ConstraintValidator
{
    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmail;

    /** @var Security */
    private $security;

    public function __construct(
        ChecksUniqueUsersEmail $checksUniqueUsersEmail,
        Security $security
    ) {
        $this->checksUniqueUsersEmail = $checksUniqueUsersEmail;
        $this->security = $security;
    }

    /**
     * @param string                             $email
     * @param Constraint|UniqueCurrentUsersEmail $constraint
     */
    public function validate($email, Constraint $constraint): void
    {
        $userId = ($this->checksUniqueUsersEmail)(Email::fromString($email));

        if ($userId && !$this->security->getUser()->userId()->sameValueAs($userId)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
