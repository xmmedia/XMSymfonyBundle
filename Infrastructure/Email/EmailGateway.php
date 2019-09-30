<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Email;

use Postmark\PostmarkClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

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
        Email $to,
        array $templateData
    ): EmailGatewayMessageId {
        $headers = [];

        if (!$this->isProduction() && !$this->isWhitelistedAddress($to)) {
            $headers['X-Original-To'] = $to->withName();

            $to = Email::fromString($this->devEmail);
        }

        $templateData = $this->setGlobalTemplateData($templateData);

        $result = $this->client->sendEmailWithTemplate(
            $this->from->withName(),
            $to->withName(),
            $templateIdOrAlias,
            $templateData,
            true,
            null,
            true,
            null,
            null,
            null,
            $headers
        );

        return EmailGatewayMessageId::fromString($result->messageId);
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
