<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Handler;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailTemplate;
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

    /** @var EmailGatewayInterface|\App\Infrastructure\Email\EmailGateway */
    private $emailGateway;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var TokenGeneratorInterface|\App\Security\TokenGenerator */
    private $tokenGenerator;

    public function __construct(
        UserList $userRepo,
        EmailGatewayInterface $emailGateway,
        RouterInterface $router,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->userRepo = $userRepo;
        $this->emailGateway = $emailGateway;
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
            EmailTemplate::USER_INVITE,
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
