<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Doctrine;

use Doctrine\DBAL\Connection;

class PdoProvider
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(): \PDO
    {
        if (method_exists($this->connection, 'getNativeConnection')) {
            $connection = $this->connection->getNativeConnection();
        } else {
            // @phpstan-ignore-next-line
            $connection = $this->connection->getWrappedConnection();
        }
        if ($connection instanceof \PDO) {
            return $connection;
        }

        return $connection->getWrappedConnection();
    }
}
