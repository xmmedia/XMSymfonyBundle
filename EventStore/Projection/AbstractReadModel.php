<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore\Projection;

use Doctrine\DBAL\Connection;

abstract class AbstractReadModel extends \Prooph\EventStore\Projection\AbstractReadModel
{
    /** @var string The table for this read model */
    protected const TABLE = null;

    protected Connection $connection;

    /**
     * The tables that make up the read model.
     * During the initialization check, reset and delete,
     * all the tables in this array are checked, truncated, or deleted.
     * If the array is empty on construct,
     * the TABLE constant will be put in the array.
     */
    protected array|null $tables;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        if (!isset($this->tables)) {
            $this->tables = [static::TABLE];
        }
    }

    public function isInitialized(): bool
    {
        foreach ($this->tables as $table) {
            $result = $this->connection->fetchOne(
                sprintf("SHOW TABLES LIKE '%s';", $table),
            );

            if (false === $result) {
                return false;
            }
        }

        return true;
    }

    public function reset(): void
    {
        foreach ($this->tables as $table) {
            $this->connection->executeQuery(
                sprintf('TRUNCATE TABLE `%s`;', $table),
            );
        }
    }

    public function delete(): void
    {
        foreach ($this->tables as $table) {
            $this->connection->executeQuery(
                sprintf('DROP TABLE IF EXISTS `%s`;', $table),
            );
        }
    }
}
