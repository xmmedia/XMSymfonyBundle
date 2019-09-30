<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

interface EmailGatewayInterface
{
    public function send(
        int $templateId,
        Email $to,
        array $templateData
    ): EmailGatewayMessageId;
}
