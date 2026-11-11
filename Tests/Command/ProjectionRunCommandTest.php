<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Xm\SymfonyBundle\Command\ProjectionRunCommand;
use Xm\SymfonyBundle\Infrastructure\Service\ProjectionRunner;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class ProjectionRunCommandTest extends BaseTestCase
{
    public function testProjectionNameOrRunAllRequired(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('configure');
        $projectionRunner->shouldNotReceive('getAllProjectionNames');

        $tester = new CommandTester(new ProjectionRunCommand($projectionRunner));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('A projection name or --run-all to run all projections is required.');

        $tester->execute([]);
    }
}
