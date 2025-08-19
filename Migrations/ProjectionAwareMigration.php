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
 *          $this->rebuildProjection('user');
 *     }
 * }
 */
trait ProjectionAwareMigration
{
    private ContainerInterface $container;

    abstract public function write(string $message): void;

    protected function rebuildProjection(string $projection): void
    {
        if (!isset($this->kernel) && !isset($this->container)) {
            throw new \RuntimeException(sprintf('The migration %s must have a $kernel property (Symfony >=6.4) or implement %s (Symfony <6.4) to rebuild projections', self::class, ContainerAwareInterface::class));
        }

        $this->write('Rebuilding projection: '.$projection);

        ini_set('memory_limit', '-1');

        if (isset($this->kernel)) {
            $kernel = $this->kernel;
        } else {
            $kernel = $this->container->get('kernel');
        }

        $application = new Application($kernel);
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

    /**
     * @deprecated use ContainerAwareTrait instead
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        if (null === $container) {
            throw new \RuntimeException('The container must be set for the migration to work. NULL received.');
        }

        $this->container = $container;
    }
}
