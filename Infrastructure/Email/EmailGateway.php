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

    /** @var string|null */
    private $devEmail;

    public function __construct(
        string $postmarkApiKey,
        string $emailFrom,
        string $emailFromName,
        string $kernelEnv,
        RouterInterface $router,
        ?string $devEmail = null
    ) {
        $this->client = new PostmarkClient($postmarkApiKey);
        $this->kernelEnv = $kernelEnv;
        $this->from = Email::fromString($emailFrom, $emailFromName);
        $this->router = $router;

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
        $whitelist = [
            // @todo-symfony
            '/@xmmedia\.com$/',
        ];

        foreach ($whitelist as $pattern) {
            if (preg_match($pattern, $to->toString())) {
                return true;
            }
        }

        return false;
    }

    private function setGlobalTemplateData(array $data): array
    {
        // @todo-symfony
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
        $default['productName'] = 'Symfony Starter';
        $default['copyrightYear'] = date('Y');
        $default['companyName'] = 'XM Media Inc.';
        $default['companyAddress'] = '123 Street, Big City';

        return array_merge($default, $data);
    }

    private function isProduction(): bool
    {
        return 'prod' === $this->kernelEnv;
    }
}
