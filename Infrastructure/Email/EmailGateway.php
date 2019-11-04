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
    /** @var PostmarkClient */
    private $client;

    /** @var Email */
    protected $from;

    /** @var string */
    protected $kernelEnv;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var string */
    private $productName;

    /** @var string */
    private $companyName;

    /** @var string */
    private $companyAddress;

    /** @var array */
    private $whitelist;

    /** @var string|null */
    private $devEmail;

    public function __construct(
        string $postmarkApiKey,
        string $emailFrom,
        string $emailFromName,
        string $kernelEnv,
        RouterInterface $router,
        string $productName,
        string $companyName,
        string $companyAddress,
        array $whitelist,
        ?string $devEmail = null
    ) {
        $this->client = new PostmarkClient($postmarkApiKey);
        $this->kernelEnv = $kernelEnv;
        $this->from = Email::fromString($emailFrom, $emailFromName);
        $this->router = $router;

        $this->productName = $productName;
        $this->companyName = $companyName;
        $this->companyAddress = $companyAddress;
        $this->whitelist = $whitelist;
        $this->devEmail = $devEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function send(
        $templateIdOrAlias,
        $to,
        array $templateData,
        ?array $attachments = null,
        ?Email $from = null
    ): EmailGatewayMessageId {
        $headers = [];

        if (!\is_array($to)) {
            $to = [$to];
        }

        Assert::allIsInstanceOf(
            $to,
            Email::class,
            'All to addresses must be instances of '.Utils::printSafe(
                Email::class
            ).'. Got %s'
        );

        if (!$this->isProduction()) {
            $headers['X-Original-To'] = implode(
                ', ',
                array_map(function (Email $email): string {
                    return $email->withName();
                }, $to)
            );

            $to = $this->removeNonWhiteListedAddresses($to);
        }

        $toString = implode(
            ', ',
            array_map(function (Email $email): string {
                return $email->withName();
            }, $to)
        );

        if (null === $from) {
            $fromString = $this->from->withName();
        } else {
            $fromString = $from->withName();
        }

        $templateData = $this->setGlobalTemplateData($templateData);

        Assert::allIsInstanceOf(
            $attachments,
            PostmarkAttachment::class,
            'All attachments must be instances of '.Utils::printSafe(
                PostmarkAttachment::class
            )
        );

        $result = $this->client->sendEmailWithTemplate(
            $fromString,
            $toString,
            $templateIdOrAlias,
            $templateData,
            true,
            null,
            true,
            null,
            null,
            null,
            $headers,
            $attachments
        );

        return EmailGatewayMessageId::fromString($result->messageId);
    }

    private function removeNonWhiteListedAddresses(array $to): array
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

    private function isWhitelistedAddress(Email $to): bool
    {
        foreach ($this->whitelist as $pattern) {
            if (preg_match($pattern, $to->toString())) {
                return true;
            }
        }

        return false;
    }

    private function setGlobalTemplateData(array $data): array
    {
        $default['supportEmail'] = $this->from->email();
        $default['rootUrl'] = $this->router->generate(
            'index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $default['indexUrl'] = $this->router->generate(
            'index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $default['productName'] = $this->productName;
        $default['copyrightYear'] = date('Y');
        $default['companyName'] = $this->companyName;
        $default['companyAddress'] = $this->companyAddress;

        return array_merge($default, $data);
    }

    private function isProduction(): bool
    {
        return 'prod' === $this->kernelEnv;
    }
}
