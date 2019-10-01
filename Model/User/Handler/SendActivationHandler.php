<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Handler;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Command\SendActivation;
use Xm\SymfonyBundle\Model\User\Exception\UserNotFound;
use Xm\SymfonyBundle\Model\User\UserList;
use Xm\SymfonyBundle\Security\TokenGeneratorInterface;
use Xm\SymfonyBundle\Util\StringUtil;

class SendActivationHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var EmailGatewayInterface|\Xm\SymfonyBundle\Infrastructure\Email\EmailGateway */
    private $emailGateway;

    /** @var string|int */
    private $templateIdOrAlias;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var TokenGeneratorInterface|\Xm\SymfonyBundle\Security\TokenGenerator */
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

    public function __invoke(SendActivation $command): void
    {
        $user = $this->userRepo->get($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $name = StringUtil::trim(sprintf(
            '%s %s',
            $command->firstName(),
            $command->lastName()
        ));
        $token = ($this->tokenGenerator)();

        $verifyUrl = $this->router->generate(
            'user_verify',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $messageId = $this->emailGateway->send(
            $this->templateIdOrAlias,
            Email::fromString($command->email()->toString(), $name),
            [
                'verifyUrl' => $verifyUrl,
                'name'      => $name,
                'email'     => $command->email()->toString(),
            ]
        );

        $user->inviteSent($token, $messageId);

        $this->userRepo->save($user);
    }
}
