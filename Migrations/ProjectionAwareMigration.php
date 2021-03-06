<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Migrations;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait ProjectionAwareMigration
{
    /** @var ContainerInterface */
    private $container;

    abstract public function write(string $message): void;

    protected function rebuildProjection(string $projection): void
    {
        if (!isset($this->container)) {
            throw new \RuntimeException(sprintf('The migration must implement the %s class to use %s', ContainerAwareInterface::class, self::class));
        }

        $this->write('Rebuilding projection: '.$projection);

        $application = new Application($this->container->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command'         => 'app:projection:rebuild',
            'projection-name' => $projection,
        ]);

        $output = new BufferedOutput();
        $return = $application->run($input, $output);

        $this->write($output->fetch());

        if (0 !== $return) {
            throw new \RuntimeException('Rebuilding projection failed!');
        }
    }

    public function setContainer(ContainerInterface $container = null)
    {
        if (null === $container) {
            throw new \RuntimeException('The container must be set for the migration to work. NULL received.');
        }

        $this->container = $container;
    }
}
