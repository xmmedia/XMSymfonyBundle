<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Handler;

use Mockery;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\User\Command\SendActivation;
use Xm\SymfonyBundle\Model\User\Exception\UserNotFound;
use Xm\SymfonyBundle\Model\User\Handler\SendActivationHandler;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Model\User\UserList;
use Xm\SymfonyBundle\Security\TokenGeneratorInterface;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class SendActivationHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('inviteSent')
            ->once();

        $command = SendActivation::now(
            $faker->userId,
            $faker->emailVo,
            Name::fromString($faker->name),
            Name::fromString($faker->name)
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        $emailGateway = new SendActivationHandlerTestEmailGateway();
        $tokenGenerator = new SendActivationHandlerTestTokenGenerator();

        $router = Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->andReturn('url');

        $handler = new SendActivationHandler(
            $repo,
            $emailGateway,
            $faker->string(10),
            $router,
            $tokenGenerator
        );

        $handler($command);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = SendActivation::now(
            $faker->userId,
            $faker->emailVo,
            Name::fromString($faker->name),
            Name::fromString($faker->name)
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $emailGateway = new SendActivationHandlerTestEmailGateway();
        $router = Mockery::mock(RouterInterface::class);
        $tokenGenerator = new SendActivationHandlerTestTokenGenerator();

        $this->expectException(UserNotFound::class);

        $handler = new SendActivationHandler(
            $repo,
            $emailGateway,
            $faker->string(10),
            $router,
            $tokenGenerator
        );

        $handler($command);
    }
}

class SendActivationHandlerTestEmailGateway implements EmailGatewayInterface
{
    public function send(
        $templateIdOrAlias,
        Email $to,
        array $templateData
    ): EmailGatewayMessageId {
        return EmailGatewayMessageId::fromString(Uuid::uuid4()->toString());
    }
}

class SendActivationHandlerTestTokenGenerator implements TokenGeneratorInterface
{
    public function __invoke(): Token
    {
        return Token::fromString('string');
    }
}
