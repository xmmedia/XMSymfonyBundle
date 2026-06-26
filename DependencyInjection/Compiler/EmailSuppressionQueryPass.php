<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Query\EmailSuppressionQuery;
use Xm\SymfonyBundle\Infrastructure\Service\EmailSuppressionCheckerInterface;

class EmailSuppressionQueryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(EmailSuppressionQuery::class)) {
            return;
        }

        if ($container->has(EmailSuppressionCheckerInterface::class)) {
            return;
        }

        // No implementation of EmailSuppressionCheckerInterface registered;
        // remove the query so the container compiles without error.
        $container->removeDefinition(EmailSuppressionQuery::class);
    }
}