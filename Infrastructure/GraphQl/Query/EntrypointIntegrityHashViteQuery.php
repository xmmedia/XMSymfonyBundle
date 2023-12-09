<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Pentatrion\ViteBundle\Service\EntrypointsLookup;
use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;

class EntrypointIntegrityHashViteQuery implements QueryInterface
{
    private EntrypointsLookup $lookup;

    public function __construct(EntrypointsLookupCollection $collection)
    {
        $this->lookup = $collection->getEntrypointsLookup();
    }

    public function __invoke(string $entrypoint): ?string
    {
        $fileData = $this->lookup->getJSFiles($entrypoint);

        if (empty($fileData)) {
            return null;
        }

        return $this->lookup->getFileHash($fileData[0]);
    }
}
