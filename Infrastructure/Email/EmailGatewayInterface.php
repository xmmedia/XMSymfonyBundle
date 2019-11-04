<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Postmark\Models\PostmarkAttachment;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

interface EmailGatewayInterface
{
    /**
     * @param int|string           $templateIdOrAlias
     * @param Email[]|Email        $to
     * @param PostmarkAttachment[] $attachments
     */
    public function send(
        $templateIdOrAlias,
        $to,
        array $templateData,
        ?array $attachments = null,
        ?Email $from = null
    ): EmailGatewayMessageId;
}
