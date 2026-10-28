<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Postmark\Models\PostmarkAttachment;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

interface EmailGatewayInterface
{
    /**
     * @param int|string|\BackedEnum $templateIdOrAlias a template ID, an alias, or an enum backed by either
     * @param Email[]|Email          $to
     * @param PostmarkAttachment[]   $attachments
     */
    public function send(
        int|string|\BackedEnum $templateIdOrAlias,
        Email|array $to,
        array $templateData,
        ?array $attachments = null,
        ?Email $from = null,
        ?Email $replyTo = null,
        array $headers = [],
        array $options = [],
    ): EmailGatewayMessageId;

    /**
     * Generates a unique reference email address.
     */
    public function getReferencesEmail(Email $emailFrom): string;
}
