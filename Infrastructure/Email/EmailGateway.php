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
use Xm\SymfonyBundle\Util\Utils;

class EmailGateway implements EmailGatewayInterface
{
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
        private ?string $devEmail = null,
    ) {
        $this->client = new PostmarkClient($postmarkApiKey);
        $this->from = Email::fromString($emailFrom, $emailFromName);
    }

    public function send(
        int|string $templateIdOrAlias,
        Email|array $to,
        array $templateData,
        array $attachments = null,
        Email $from = null,
        Email $replyTo = null,
    ): EmailGatewayMessageId {
        $headers = [];

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
                array_map(function (Email $email): string {
                    return $email->withName();
                }, $to),
            );

            $to = $this->removeNonWhiteListedAddresses($to);
        }

        $toString = implode(
            ', ',
            array_map(function (Email $email): string {
                return $email->withName();
            }, $to),
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

        $result = $this->client->sendEmailWithTemplate(
            $fromString,
            $toString,
            $templateIdOrAlias,
            $templateData,
            true,
            null,
            true,
            $replyTo ? $replyTo->toString() : null,
            null,
            null,
            $headers,
            $attachments,
        );

        return EmailGatewayMessageId::fromString($result->messageId);
    }

    protected function removeNonWhiteListedAddresses(array $to): array
    {
        foreach ($to as $key => $toEmail) {
            if (!$this->isWhitelistedAddress($toEmail)) {
                unset($to[$key]);
            }
        }

        if (empty($to)) {
            $to = [Email::fromString($this->devEmail)];
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
