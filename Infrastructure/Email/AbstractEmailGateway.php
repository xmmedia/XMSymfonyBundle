<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Model\Email;

abstract class AbstractEmailGateway implements EmailGatewayInterface
{
    /**
     * Generates a unique reference email address.
     */
    public function getReferencesEmail(Email $emailFrom): string
    {
        $email = $emailFrom->toString();

        return sprintf(
            '<%s@%s>',
            Uuid::uuid4()->toString(),
            substr($email, strpos($email, '@') + 1),
        );
    }
}
