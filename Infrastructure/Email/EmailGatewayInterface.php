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
     * @param array{
     *     prevent_threading?: bool,
     *     tag?: string,
     *     track_links?: 'None'|'HtmlAndText'|'HtmlOnly'|'TextOnly',
     *     metadata?: array<string, string>,
     * } $options `prevent_threading` adds a unique `References` header, so clients don't thread the email;
     *   `track_links` replaces the Postmark server's default (opens are always tracked);
     *   `metadata` comes back in Postmark's webhooks, eg to match them to a record
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
