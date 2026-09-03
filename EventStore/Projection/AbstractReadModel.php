<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore\Projection;

use Doctrine\DBAL\Connection;

abstract class AbstractReadModel extends \Prooph\EventStore\Projection\AbstractReadModel
{
    /** @var string The table for this read model */
    protected const TABLE = null;

    /** @var Connection */
    protected $connection;

    /**
     * The tables that make up the read model.
     * During the initialization check, reset and delete,
     * all the tables in this array are checked, truncated, or deleted.
     * If the array is empty on construct,
     * the TABLE constant will be put in the array.
     *
     * @var string[]|null
     */
    protected $tables;

    /**
     * Shadows the parent's private stack so persist() can be made atomic.
     *
     * @var array<int, array{0: string, 1: array}>
     */
    private $stack = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        if (null === $this->tables) {
            $this->tables = [static::TABLE];
        }
    }

    public function stack(string $operation, ...$args): void
    {
        $this->stack[] = [$operation, $args];
    }

    /**
     * Applies the stacked operations all-or-nothing.
     *
     * The projector only advances its stream position after this returns,
     * so if any operation fails nothing may remain written: otherwise the next
     * run replays the same events against the partial writes and fails again
     * on duplicate keys. The stack is always cleared, even on failure, because
     * this read model instance is shared by every projection run in the same
     * process and leftover operations would poison the next run.
     */
    public function persist(): void
    {
        try {
            $this->connection->transactional(function (): void {
                foreach ($this->stack as [$operation, $args]) {
                    $this->{$operation}(...$args);
                }
            });
        } finally {
            $this->stack = [];
        }
    }

    public function isInitialized(): bool
    {
        foreach ($this->tables as $table) {
            $result = $this->connection->fetchOne(
                \sprintf("SHOW TABLES LIKE '%s';", $table)
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
                \sprintf('TRUNCATE TABLE `%s`;', $table)
            );
        }
    }

    public function delete(): void
    {
        foreach ($this->tables as $table) {
            $this->connection->executeQuery(
                \sprintf('DROP TABLE IF EXISTS `%s`;', $table)
            );
        }
    }
}
