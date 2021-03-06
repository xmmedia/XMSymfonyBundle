<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xm\SymfonyBundle\Infrastructure\Service\Cloudflare;

final class PurgeCloudflareCacheCommand extends Command
{
    /** @var Cloudflare */
    private $cloudflare;

    public function __construct(Cloudflare $cloudflare = null)
    {
        parent::__construct();

        $this->cloudflare = $cloudflare;
    }

    protected function configure()
    {
        $this
            ->setName('app:cloudflare:purge-cache')
            ->setDescription('Purges the entire Cloudflare cache.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Purge Cloudflare Cache');

        if (null === $this->cloudflare) {
            throw new \RuntimeException('The Cloudflare service must be configured before using this command.');
        }

        if (!$this->cloudflare->clearCache()) {
            $io->error('Failed to clear the cache.');
        }

        $io->success('Cache Purged');

        return 0;
    }
}
