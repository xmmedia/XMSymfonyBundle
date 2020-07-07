<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Xm\SymfonyBundle\Model\Email;

final class GraphQlDumpSchemaCommand extends Command
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $em
    ) {
        parent::__construct();

        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setName('app:graphql:dump-schema')
            ->setDescription('Dumps GraphQL schema')
            ->addArgument(
                'user-email',
                InputArgument::REQUIRED,
                'The user to use for the permission checks in the GraphQL config. Typically an admin user.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->tokenStorage->setToken(
            $this->token($input->getArgument('user-email'))
        );

        $command = $this->getApplication()->find('graphql:dump-schema');

        $arguments = [
            'command'  => 'graphql:dump-schema',
            '--file'   => 'graphql.schema.json',
            '--modern' => true,
        ];

        return $command->run(new ArrayInput($arguments), $output);
    }

    private function token(string $userEmail): PostAuthenticationGuardToken
    {
        $userFinder = $this->em->getRepository('App\Entity\User');

        $user = $userFinder->findOneByEmail(Email::fromString($userEmail));

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('The user with email "%s" cannot be found.', $userEmail));
        }

        return new PostAuthenticationGuardToken(
            $user,
            'app_provider',
            $user->roles()
        );
    }
}
