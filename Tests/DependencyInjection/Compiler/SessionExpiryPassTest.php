<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DependencyInjection\Compiler;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\RememberMeFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Xm\SymfonyBundle\DependencyInjection\Compiler\SessionExpiryPass;
use Xm\SymfonyBundle\Security\SessionExpiry;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class SessionExpiryPassTest extends BaseTestCase
{
    /**
     * Uses SecurityBundle's own factory, so this fails if Symfony changes where the name is.
     */
    public function testCookieNamesFromFirewalls(): void
    {
        $cookie = $this->faker()->slug();
        $container = $this->container();
        $this->addRememberMe($container, 'main', ['name' => $cookie]);
        $this->addRememberMe($container, 'other', []);

        (new SessionExpiryPass())->process($container);

        $this->assertSame(
            [$cookie, 'REMEMBERME'],
            $container->getDefinition(SessionExpiry::class)->getArgument('$rememberMeCookies'),
        );
    }

    public function testNoRememberMe(): void
    {
        $container = $this->container();

        (new SessionExpiryPass())->process($container);

        $this->assertSame([], $container->getDefinition(SessionExpiry::class)->getArgument('$rememberMeCookies'));
    }

    public function testCustomHandlerService(): void
    {
        $container = $this->container();
        $container->register('custom_handler')
            ->addTag(SessionExpiryPass::HANDLER_TAG, ['firewall' => 'main']);

        (new SessionExpiryPass())->process($container);

        $this->assertSame([], $container->getDefinition(SessionExpiry::class)->getArgument('$rememberMeCookies'));
    }

    public function testSessionExpiryDisabled(): void
    {
        $container = new ContainerBuilder();
        $this->addRememberMe($container, 'main', []);

        (new SessionExpiryPass())->process($container);

        $this->assertFalse($container->hasDefinition(SessionExpiry::class));
    }

    private function container(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->register(SessionExpiry::class);

        return $container;
    }

    private function addRememberMe(ContainerBuilder $container, string $firewall, array $config): void
    {
        $factory = new RememberMeFactory();

        $treeBuilder = new TreeBuilder('remember_me');
        $factory->addConfiguration($treeBuilder->getRootNode());
        $config = (new Processor())->process(
            $treeBuilder->buildTree(),
            [['secret' => $this->faker()->password()] + $config],
        );

        $factory->createAuthenticator($container, $firewall, $config, 'user_provider');
    }
}
