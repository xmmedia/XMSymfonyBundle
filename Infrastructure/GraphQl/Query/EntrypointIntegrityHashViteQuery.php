<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Pentatrion\ViteBundle\Asset\EntrypointsLookup;

class EntrypointIntegrityHashViteQuery implements QueryInterface
{
    private EntrypointsLookup $lookup;

    public function __construct(EntrypointsLookup $lookup)
    {
        $this->lookup = $lookup;
    }

    public function __invoke(string $entrypoint): ?string
    {
        $fileData = $this->lookup->getJSFiles($entrypoint);

        if (empty($fileData)) {
            return null;
        }

        return $fileData[0]['hash'];
    }
}
