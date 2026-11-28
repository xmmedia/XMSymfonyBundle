<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Postmark\Models\PostmarkAttachment;
use Postmark\PostmarkClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Util\Assert;
use Xm\SymfonyBundle\Util\StringUtil;
use Xm\SymfonyBundle\Util\Utils;

class EmailGateway extends AbstractEmailGateway implements EmailGatewayInterface
{
    /** Postmark's TrackLinks values. */
    public const array TRACK_LINKS = ['None', 'HtmlAndText', 'HtmlOnly', 'TextOnly'];

    protected PostmarkClient $client;
    protected Email $from;

    public function __construct(
        string $postmarkApiKey,
        string $emailFrom,
        string $emailFromName,
        private readonly string $kernelEnv,
        private readonly RouterInterface $router,
        private readonly string $productName,
        private readonly string $companyName,
        private readonly string $companyAddress,
        private readonly array $whitelist,
        private readonly ?string $devEmail = null,
    ) {
        $this->client = new PostmarkClient($postmarkApiKey);
        $this->from = Email::fromString($emailFrom, $emailFromName);
    }

    #[\Override]
    public function send(
        int|string|\BackedEnum $templateIdOrAlias,
        Email|array $to,
        array $templateData,
        ?array $attachments = null,
        ?Email $from = null,
        ?Email $replyTo = null,
        array $headers = [],
        array $options = [],
    ): EmailGatewayMessageId {
        if ($templateIdOrAlias instanceof \BackedEnum) {
            $templateIdOrAlias = $templateIdOrAlias->value;
        }

        if (!\is_array($to)) {
            $to = [$to];
        }

        Assert::allIsInstanceOf(
            $to,
            Email::class,
            'All to addresses must be instances of '.Utils::printSafe(
                Email::class,
            ).'. Got %s',
        );

        if (!$this->isProduction()) {
            $headers['X-Original-To'] = implode(
                ', ',
                array_map(static fn (Email $email): string => $email->withName(), $to),
            );

            $to = $this->removeNonWhiteListedAddresses($to);
        }

        if ($options['prevent_threading'] ?? false) {
            $headers['References'] = $this->getReferencesEmail($this->from);
        }

        $toString = implode(
            ', ',
            array_map(static fn (Email $email): string => $email->withName(), $to),
        );

        if (null === $from) {
            $fromString = $this->from->withName();
        } else {
            $fromString = $from->withName();
        }

        $templateData = $this->setGlobalTemplateData($templateData);

        Assert::allScalarRecursive($templateData, 'All of the template data must be scalars. Got %s');

        if (null !== $attachments) {
            Assert::allIsInstanceOf(
                $attachments,
                PostmarkAttachment::class,
                'All attachments must be instances of '.Utils::printSafe(
                    PostmarkAttachment::class,
                ),
            );
        }

        $trackLinks = $options['track_links'] ?? null;
        Assert::nullOrInArray(
            $trackLinks,
            self::TRACK_LINKS,
            'The track_links option must be one of %2$s. Got %s',
        );

        $metadata = $options['metadata'] ?? null;
        if (null !== $metadata) {
            Assert::isMap($metadata, 'The metadata option must be an array keyed by strings. Got %s');
            Assert::allString($metadata, 'All of the metadata values must be strings. Got %s');
        }

        $result = $this->client->sendEmailWithTemplate(
            $fromString,
            $toString,
            $templateIdOrAlias,
            $templateData,
            true,
            $options['tag'] ?? null,
            true,
            $replyTo ? $replyTo->toString() : null,
            null,
            null,
            $headers,
            $attachments,
            $trackLinks,
            $metadata,
        );

        return EmailGatewayMessageId::fromString($result->getMessageID());
    }

    protected function removeNonWhiteListedAddresses(array $to): array
    {
        foreach ($to as $key => $toEmail) {
            if (!$this->isWhitelistedAddress($toEmail)) {
                unset($to[$key]);
            }
        }

        if ([] === $to) {
            $devEmail = StringUtil::trim($this->devEmail);

            if (null === $devEmail) {
                throw new \RuntimeException('None of the recipients are in the email whitelist & no dev email is set to send it to instead. Set the dev email (usually DEV_EMAIL in .env.local) or add the recipient to the whitelist.');
            }

            return [Email::fromString($devEmail)];
        }

        return $to;
    }

    protected function isWhitelistedAddress(Email $to): bool
    {
        foreach ($this->whitelist as $pattern) {
            if (preg_match($pattern, $to->toString())) {
                return true;
            }
        }

        return false;
    }

    protected function setGlobalTemplateData(array $data): array
    {
        $default['supportEmail'] = $this->from->email();
        $default['rootUrl'] = $this->router->generate(
            'index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        $default['indexUrl'] = $this->router->generate(
            'index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        $default['productName'] = $this->productName;
        $default['copyrightYear'] = date('Y');
        $default['companyName'] = $this->companyName;
        $default['companyAddress'] = $this->companyAddress;

        return array_merge($default, $data);
    }

    protected function isProduction(): bool
    {
        return 'prod' === $this->kernelEnv;
    }
}
