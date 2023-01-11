<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

/**
 * @deprecated use EntrypointIntegrityHashQuery instead with overblog/graphql-bundle ^0.14
 */
class EntrypointIntegrityHashResolver implements ResolverInterface
{
    /** @var EntrypointLookupInterface */
    private $entrypointLookup;

    public function __construct(EntrypointLookupCollectionInterface $collection)
    {
        $this->entrypointLookup = $collection->getEntrypointLookup();
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
