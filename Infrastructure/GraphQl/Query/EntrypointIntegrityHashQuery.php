<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class EntrypointIntegrityHashQuery implements QueryInterface
{
    private EntrypointLookupInterface $entrypointLookup;

    public function __construct(?EntrypointLookupCollectionInterface $collection = null)
    {
        if (null !== $collection) {
            $this->entrypointLookup = $collection->getEntrypointLookup();
        }
    }

    public function __invoke(string $entrypoint): ?string
    {
        $path = $this->entrypointLookup->getJavaScriptFiles($entrypoint)[0];
        $hashes = $this->entrypointLookup->getIntegrityData();

        if (empty($hashes)) {
            return null;
        }

        return $hashes[$path];
    }
}
