<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Postmark\Models\PostmarkAttachment;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

interface EmailGatewayInterface
{
    /**
     * @param Email[]|Email        $to
     * @param PostmarkAttachment[] $attachments
     */
    public function send(
        int|string $templateIdOrAlias,
        Email|array $to,
        array $templateData,
        array $attachments = null,
        Email $from = null,
        Email $replyTo = null,
    ): EmailGatewayMessageId;
}
