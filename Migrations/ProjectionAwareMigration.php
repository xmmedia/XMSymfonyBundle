<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Migrations;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Used for migrations that need to rebuild projections.
 *
 * Example usage:
 * final class Version20230201002 extends AbstractMigration implements ContainerAwareInterface
 * {
 *     use ProjectionAwareMigration;
 *     // ...
 *     public function postUp(Schema $schema): void
 *     {
 *          $this->createEventStream('user');
 *          $this->runProjection('user');
 *          $this->rebuildProjection('user');
 *     }
 * }
 */
trait ProjectionAwareMigration
{
    private ContainerInterface $container;

    abstract public function write(string $message): void;

    protected function createEventStream(string $streamName): void
    {
        $this->write('Creating event stream: '.$streamName);

        $this->runCommand(
            [
                'command'     => 'event-store:event-stream:create',
                'stream_name' => $streamName,
            ],
            'Creating event stream failed!',
        );
    }

    protected function runProjection(string $projection): void
    {
        $this->write('Running projection: '.$projection);

        $this->runCommand(
            [
                'command'         => 'event-store:projection:run',
                'projection-name' => $projection,
                '--run-once'      => true,
            ],
            'Running projection failed!',
        );
    }

    protected function rebuildProjection(string $projection): void
    {
        $this->write('Rebuilding projection: '.$projection);

        ini_set('memory_limit', '-1');

        $this->runCommand(
            [
                'command'         => 'app:projection:rebuild',
                'projection-name' => $projection,
            ],
            'Rebuilding projection failed!',
        );
    }

    private function runCommand(array $parameters, string $errorMessage): void
    {
        if (!isset($this->kernel) && !isset($this->container)) {
            throw new \RuntimeException(sprintf('The migration %s must have a $kernel property (Symfony >=6.4) or implement %s (Symfony <6.4) to rebuild projections', self::class, ContainerAwareInterface::class));
        }

        $kernel = $this->kernel ?? $this->container->get('kernel');

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $output = new BufferedOutput();
        $return = $application->run(new ArrayInput($parameters), $output);

        $this->write($output->fetch());

        if (0 !== $return) {
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * @deprecated use ContainerAwareTrait instead
     */
    public function setContainer(?ContainerInterface $container = null): void
    {
        if (null === $container) {
            throw new \RuntimeException('The container must be set for the migration to work. NULL received.');
        }

        $this->container = $container;
    }
}
