<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class EntrypointIntegrityHashQuery implements QueryInterface
{
    private readonly ?EntrypointLookupInterface $entrypointLookup;

    public function __construct(?EntrypointLookupCollectionInterface $collection = null)
    {
        $this->entrypointLookup = $collection?->getEntrypointLookup();
    }

    public function __invoke(string $entrypoint): ?string
    {
        if (null === $this->entrypointLookup) {
            return null;
        }

        $path = $this->entrypointLookup->getJavaScriptFiles($entrypoint)[0] ?? null;

        if (null === $path) {
            return null;
        }

        return $this->entrypointLookup->getIntegrityData()[$path] ?? null;
    }
}
