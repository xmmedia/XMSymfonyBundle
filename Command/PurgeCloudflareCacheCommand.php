<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xm\SymfonyBundle\Infrastructure\Service\Cloudflare;

#[AsCommand(
    name: 'app:cloudflare:purge-cache',
    description: 'Purges the entire Cloudflare cache.',
)]
final class PurgeCloudflareCacheCommand extends Command
{
    public function __construct(private Cloudflare|null $cloudflare = null)
    {
        parent::__construct();
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
