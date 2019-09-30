<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

interface EmailGatewayInterface
{
    /**
     * @param int|string $templateIdOrAlias
     */
    public function send(
        $templateIdOrAlias,
        Email $to,
        array $templateData
    ): EmailGatewayMessageId;
}
