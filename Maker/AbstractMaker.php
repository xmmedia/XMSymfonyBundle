<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Maker;

abstract class AbstractMaker extends \Symfony\Bundle\MakerBundle\Maker\AbstractMaker
{
    protected function doubleEscapeClass(string $class): string
    {
        return str_replace('\\', '\\\\', $class);
    }

    protected function skeletonPath(): string
    {
        return __DIR__.'/../Resources/skeleton/';
    }
}
