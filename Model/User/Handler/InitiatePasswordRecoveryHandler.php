<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Handler;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Command\InitiatePasswordRecovery;
use Xm\SymfonyBundle\Model\User\Exception\UserNotFound;
use Xm\SymfonyBundle\Model\User\UserList;
use Xm\SymfonyBundle\Security\TokenGeneratorInterface;

class InitiatePasswordRecoveryHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var EmailGatewayInterface|\App\Infrastructure\Email\EmailGateway */
    private $emailGateway;

    /** @var string|int */
    private $templateIdOrAlias;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var TokenGeneratorInterface|\App\Security\TokenGenerator */
    private $tokenGenerator;

    public function __construct(
        UserList $userRepo,
        EmailGatewayInterface $emailGateway,
        $templateIdOrAlias,
        RouterInterface $router,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->userRepo = $userRepo;
        $this->emailGateway = $emailGateway;
        $this->templateIdOrAlias = $templateIdOrAlias;
        $this->router = $router;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function __invoke(InitiatePasswordRecovery $command): void
    {
        $user = $this->userRepo->get($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $token = ($this->tokenGenerator)();

        $resetUrl = $this->router->generate(
            'user_reset',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $messageId = $this->emailGateway->send(
            $this->templateIdOrAlias,
            Email::fromString($command->email()->toString()),
            [
                'resetUrl' => $resetUrl,
                'email'    => $command->email()->toString(),
            ]
        );

        $user->passwordRecoverySent($token, $messageId);

        $this->userRepo->save($user);
    }
}
