<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Xm\SymfonyBundle\Security\SessionExpiry;

/**
 * Passes the remember-me cookie names from the firewalls' config to SessionExpiry, so sessions
 * signed in with remember-me don't expire. Symfony has no public API for the name, so it's read
 * from the options SecurityBundle's RememberMeFactory gives each firewall's remember-me handler.
 */
class SessionExpiryPass implements CompilerPassInterface
{
    // tag & options argument of the handlers RememberMeFactory creates
    public const string HANDLER_TAG = 'security.remember_me_handler';
    public const int HANDLER_OPTIONS_ARGUMENT = 3;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SessionExpiry::class)) {
            return;
        }

        $cookies = [];
        foreach (array_keys($container->findTaggedServiceIds(self::HANDLER_TAG)) as $id) {
            $arguments = $container->getDefinition($id)->getArguments();
            // replaced arguments of a child definition are keyed "index_N"
            $options = $arguments['index_'.self::HANDLER_OPTIONS_ARGUMENT]
                ?? $arguments[self::HANDLER_OPTIONS_ARGUMENT]
                ?? null;

            // a custom handler service (remember_me.service) doesn't have the options
            if (isset($options['name'])) {
                $cookies[] = $options['name'];
            }
        }

        $container->getDefinition(SessionExpiry::class)
            ->setArgument('$rememberMeCookies', array_values(array_unique($cookies)));
    }
}
