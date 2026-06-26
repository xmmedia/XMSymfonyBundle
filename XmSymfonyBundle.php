<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Xm\SymfonyBundle\DependencyInjection\Compiler\EmailSuppressionQueryPass;

class XmSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EmailSuppressionQueryPass());
    }
}
