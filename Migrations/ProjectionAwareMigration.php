<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Migrations;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Used for migrations that need to rebuild projections.
 *
 * Example usage:
 * final class Version20230201002 extends AbstractMigration
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

    /**
     * Not $kernel: older migrations declare their own `private Kernel $kernel`, which would conflict.
     */
    private KernelInterface $projectionKernel;

    abstract public function write(string $message): void;

    /**
     * Running or rebuilding a projection creates/drops its tables, which in MySQL implicitly commits
     * the migration's transaction & leaves the projection's own transactions with nothing to commit
     * ("There is no active transaction"). Override to return true if the migration doesn't touch projections.
     */
    public function isTransactional(): bool
    {
        return false;
    }

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
                'command'         => 'app:projection:run',
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
        $kernel = $this->kernel ?? $this->projectionKernel ?? ($this->container ?? null)?->get('kernel');
        if (null === $kernel) {
            throw new \RuntimeException(\sprintf('The kernel wasn\'t set on the migration %s. Make migrations services (enable_service_migrations) or call setKernel() from a custom migration factory.', self::class));
        }

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
     * Autowired when migrations are services. Older projects call it from their own migration factory.
     */
    #[Required]
    public function setKernel(KernelInterface $kernel): void
    {
        $this->projectionKernel = $kernel;
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
