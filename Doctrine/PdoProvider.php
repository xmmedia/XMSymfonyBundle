<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Doctrine;

use Doctrine\DBAL\Connection;

class PdoProvider
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(): \PDO
    {
        $connection = $this->connection->getNativeConnection();

        if (!$connection instanceof \PDO) {
            throw new \RuntimeException(sprintf('Expecting \PDO, but got %s', get_class($connection)));
        }

        return $connection;
    }
}
