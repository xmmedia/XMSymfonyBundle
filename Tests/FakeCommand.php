<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Xm\SymfonyBundle\Messaging\Command;

class FakeCommand extends Command
{
    public static function perform(): self
    {
        return new self();
    }
}
