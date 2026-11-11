<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Email;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Postmark\Models\PostmarkAttachment;
use Postmark\Models\PostmarkResponse;
use Postmark\PostmarkClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGateway;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class EmailGatewayTest extends BaseTestCase
{
    private const string MESSAGE_ID = 'b7bc2f4a-e38e-4336-af7d-e6c392c2f817';
    private const string ROOT_URL = 'https://example.com/';
    private const array WHITELIST = ['/@xmmedia\.com$/', '/^allowed@example\.org$/'];

    public function testSend(): void
    {
        $client = $this->client(
            from: 'From Name <from@example.com>',
            to: 'Jane Doe <jane@example.org>, john@example.net',
            templateIdOrAlias: 123,
            templateModel: [
                'supportEmail'   => 'from@example.com',
                'rootUrl'        => self::ROOT_URL,
                'indexUrl'       => self::ROOT_URL,
                'productName'    => 'Product',
                'copyrightYear'  => date('Y'),
                'companyName'    => 'Company',
                'companyAddress' => '123 Street',
                'name'           => 'Jane',
            ],
        );

        $result = $this->gateway($client)->send(
            123,
            [Email::fromString('jane@example.org', 'Jane Doe'), Email::fromString('john@example.net')],
            ['name' => 'Jane'],
        );

        $this->assertInstanceOf(EmailGatewayMessageId::class, $result);
        $this->assertSame(self::MESSAGE_ID, $result->toString());
    }

    public function testSendSingleRecipient(): void
    {
        $client = $this->client(to: 'jane@example.org');

        $result = $this->gateway($client)->send(123, Email::fromString('jane@example.org'), []);

        $this->assertSame(self::MESSAGE_ID, $result->toString());
    }

    #[DataProvider('templateProvider')]
    public function testSendTemplate(int|string|\BackedEnum $template, int|string $expected): void
    {
        $client = $this->client(templateIdOrAlias: $expected);

        $this->gateway($client)->send($template, Email::fromString('jane@example.org'), []);
    }

    public static function templateProvider(): \Generator
    {
        yield 'ID'              => [123, 123];
        yield 'alias'           => ['user-invite', 'user-invite'];
        yield 'string enum'     => [EmailGatewayTestTemplate::INVITE, 'user-invite'];
        yield 'int enum'        => [EmailGatewayTestTemplateId::INVITE, 456];
    }

    public function testSendFromAndReplyTo(): void
    {
        $client = $this->client(
            from: 'Other Sender <other@example.com>',
            replyTo: 'reply@example.com',
        );

        $this->gateway($client)->send(
            123,
            Email::fromString('jane@example.org'),
            [],
            from: Email::fromString('other@example.com', 'Other Sender'),
            replyTo: Email::fromString('reply@example.com', 'Reply Name'),
        );
    }

    public function testSendTemplateDataOverridesDefaults(): void
    {
        $client = $this->client(
            templateModel: static fn (array $model): bool => 'Other Product' === $model['productName']
                && 'Company' === $model['companyName']
                && ['a' => ['b' => 1, 'c' => null]] === $model['nested'],
        );

        $this->gateway($client)->send(
            123,
            Email::fromString('jane@example.org'),
            ['productName' => 'Other Product', 'nested' => ['a' => ['b' => 1, 'c' => null]]],
        );
    }

    public function testSendHeaders(): void
    {
        $client = $this->client(headers: ['X-Custom' => 'value']);

        $this->gateway($client)->send(
            123,
            Email::fromString('jane@example.org'),
            [],
            headers: ['X-Custom' => 'value'],
        );
    }

    public function testSendPreventThreading(): void
    {
        $client = $this->client(
            headers: static fn (array $headers): bool => 1 === preg_match(
                '/^<[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}@example\.com>$/',
                $headers['References'],
            ),
        );

        $this->gateway($client)->send(
            123,
            Email::fromString('jane@example.org'),
            [],
            options: ['prevent_threading' => true],
        );
    }

    public function testSendPreventThreadingFalse(): void
    {
        $client = $this->client(headers: []);

        $this->gateway($client)->send(
            123,
            Email::fromString('jane@example.org'),
            [],
            options: ['prevent_threading' => false],
        );
    }

    public function testSendAttachments(): void
    {
        $attachments = [
            PostmarkAttachment::fromRawData('content', 'file.txt', 'text/plain'),
            PostmarkAttachment::fromRawData('other', 'other.txt', 'text/plain'),
        ];

        $client = $this->client(attachments: $attachments);

        $this->gateway($client)->send(
            123,
            Email::fromString('jane@example.org'),
            [],
            $attachments,
        );
    }

    public function testSendInvalidRecipient(): void
    {
        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldNotReceive('sendEmailWithTemplate');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('All to addresses must be instances of');

        $this->gateway($client)->send(123, [Email::fromString('jane@example.org'), 'john@example.net'], []);
    }

    #[DataProvider('invalidTemplateDataProvider')]
    public function testSendInvalidTemplateData(array $templateData): void
    {
        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldNotReceive('sendEmailWithTemplate');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('All of the template data must be scalars.');

        $this->gateway($client)->send(123, Email::fromString('jane@example.org'), $templateData);
    }

    public static function invalidTemplateDataProvider(): \Generator
    {
        yield 'object'        => [['name' => new \stdClass()]];
        yield 'nested object' => [['a' => ['b' => new \stdClass()]]];
        yield 'Email'         => [['email' => Email::fromString('jane@example.org')]];
    }

    public function testSendInvalidAttachment(): void
    {
        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldNotReceive('sendEmailWithTemplate');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('All attachments must be instances of');

        $this->gateway($client)->send(
            123,
            Email::fromString('jane@example.org'),
            [],
            [PostmarkAttachment::fromRawData('content', 'file.txt'), 'file.txt'],
        );
    }

    public function testSendProductionIgnoresWhitelist(): void
    {
        $client = $this->client(to: 'jane@example.org', headers: []);

        $this->gateway($client, 'prod', null)->send(123, Email::fromString('jane@example.org'), []);
    }

    public function testSendNotProductionRemovesNonWhitelisted(): void
    {
        $client = $this->client(
            to: 'User <user@xmmedia.com>, allowed@example.org',
            headers: [
                'X-Original-To' => 'User <user@xmmedia.com>, Jane Doe <jane@example.org>, allowed@example.org',
            ],
        );

        $this->gateway($client, 'dev')->send(
            123,
            [
                Email::fromString('user@xmmedia.com', 'User'),
                Email::fromString('jane@example.org', 'Jane Doe'),
                Email::fromString('allowed@example.org'),
            ],
            [],
        );
    }

    public function testSendNotProductionKeepsCustomHeaders(): void
    {
        $client = $this->client(
            to: 'user@xmmedia.com',
            headers: ['X-Custom' => 'value', 'X-Original-To' => 'user@xmmedia.com'],
        );

        $this->gateway($client, 'test')->send(
            123,
            Email::fromString('user@xmmedia.com'),
            [],
            headers: ['X-Custom' => 'value'],
        );
    }

    public function testSendNotProductionSendsToDevEmail(): void
    {
        $client = $this->client(
            to: 'dev@example.com',
            headers: ['X-Original-To' => 'Jane Doe <jane@example.org>, john@example.net'],
        );

        $this->gateway($client, 'dev', 'dev@example.com')->send(
            123,
            [Email::fromString('jane@example.org', 'Jane Doe'), Email::fromString('john@example.net')],
            [],
        );
    }

    #[DataProvider('paddedDevEmailProvider')]
    public function testSendNotProductionTrimsDevEmail(string $devEmail): void
    {
        $client = $this->client(to: 'dev@example.com', headers: ['X-Original-To' => 'jane@example.org']);

        $this->gateway($client, 'dev', $devEmail)->send(123, Email::fromString('jane@example.org'), []);
    }

    public static function paddedDevEmailProvider(): \Generator
    {
        yield 'spaces'           => [' dev@example.com '];
        yield 'tab & newline'    => ["\tdev@example.com\n"];
    }

    public function testSendNotProductionEmptyWhitelist(): void
    {
        $client = $this->client(to: 'dev@example.com', headers: ['X-Original-To' => 'user@xmmedia.com']);

        $this->gateway($client, 'dev', 'dev@example.com', [])->send(
            123,
            Email::fromString('user@xmmedia.com'),
            [],
        );
    }

    #[DataProvider('missingDevEmailProvider')]
    public function testSendNotProductionNoDevEmail(?string $devEmail): void
    {
        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldNotReceive('sendEmailWithTemplate');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('no dev email is set');

        $this->gateway($client, 'dev', $devEmail)->send(123, Email::fromString('jane@example.org'), []);
    }

    public static function missingDevEmailProvider(): \Generator
    {
        yield 'null'   => [null];
        yield 'empty'  => [''];
        yield 'spaces' => [' '];
    }

    public function testGetReferencesEmail(): void
    {
        $gateway = $this->gateway(\Mockery::mock(PostmarkClient::class));

        $first = $gateway->getReferencesEmail(Email::fromString('from@sub.example.com'));
        $second = $gateway->getReferencesEmail(Email::fromString('from@sub.example.com'));

        $this->assertMatchesRegularExpression('/^<[0-9a-f-]{36}@sub\.example\.com>$/', $first);
        $this->assertNotSame($first, $second);
    }

    /**
     * Expects a single send with the given arguments.
     * A closure for templateModel or headers is used as a matcher.
     */
    private function client(
        string $from = 'From Name <from@example.com>',
        string $to = 'jane@example.org',
        int|string $templateIdOrAlias = 123,
        array|\Closure|null $templateModel = null,
        ?string $replyTo = null,
        array|\Closure $headers = [],
        ?array $attachments = null,
    ): PostmarkClient&MockInterface {
        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldReceive('sendEmailWithTemplate')
            ->once()
            ->with(
                $from,
                $to,
                $templateIdOrAlias,
                match (true) {
                    null === $templateModel            => \Mockery::type('array'),
                    $templateModel instanceof \Closure => \Mockery::on($templateModel),
                    default                            => $templateModel,
                },
                true,
                null,
                true,
                $replyTo,
                null,
                null,
                $headers instanceof \Closure ? \Mockery::on($headers) : $headers,
                $attachments,
            )
            ->andReturn(new PostmarkResponse(['MessageID' => self::MESSAGE_ID]));

        return $client;
    }

    private function gateway(
        PostmarkClient $client,
        string $kernelEnv = 'prod',
        ?string $devEmail = 'dev@example.com',
        array $whitelist = self::WHITELIST,
    ): EmailGateway {
        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->with('index', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn(self::ROOT_URL);

        $gateway = new EmailGateway(
            'api-key',
            'from@example.com',
            'From Name',
            $kernelEnv,
            $router,
            'Product',
            'Company',
            '123 Street',
            $whitelist,
            $devEmail,
        );

        new \ReflectionProperty(EmailGateway::class, 'client')->setValue($gateway, $client);

        return $gateway;
    }
}

enum EmailGatewayTestTemplate: string
{
    case INVITE = 'user-invite';
}

enum EmailGatewayTestTemplateId: int
{
    case INVITE = 456;
}
