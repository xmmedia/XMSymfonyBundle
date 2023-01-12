<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore\PersistenceStrategy;

use Prooph\Common\Messaging\MessageConverter;
use Prooph\EventStore\Pdo\DefaultMessageConverter;
use Prooph\EventStore\Pdo\HasQueryHint;
use Prooph\EventStore\Pdo\PersistenceStrategy\MySqlPersistenceStrategy;
use Prooph\EventStore\StreamName;
use Xm\SymfonyBundle\Util\Json;

/**
 * THIS IS A COPY of \Prooph\EventStore\Pdo\PersistenceStrategy\MySqlSingleStreamStrategy
 * EXCEPT for generateTableName().
 */
final class StreamStrategy implements MySqlPersistenceStrategy, HasQueryHint
{
    private MessageConverter $messageConverter;

    public function __construct(?MessageConverter $messageConverter = null)
    {
        $this->messageConverter = $messageConverter ?? new DefaultMessageConverter();
    }

    /**
     * @return string[]
     */
    public function createSchema(string $tableName): array
    {
        $index = $this->indexName();

        $statement = <<<EOT
CREATE TABLE `$tableName` (
    `no` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `event_id` CHAR(36) COLLATE utf8mb4_bin NOT NULL,
    `event_name` VARCHAR(100) COLLATE utf8mb4_bin NOT NULL,
    `payload` JSON NOT NULL,
    `metadata` JSON NOT NULL,
    `created_at` DATETIME(6) NOT NULL,
    `aggregate_version` INT(11) UNSIGNED GENERATED ALWAYS AS (JSON_EXTRACT(metadata, '$._aggregate_version')) STORED NOT NULL,
    `aggregate_id` CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(metadata, '$._aggregate_id'))) STORED NOT NULL,
    `aggregate_type` VARCHAR(100) GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(metadata, '$._aggregate_type'))) STORED NOT NULL,
    PRIMARY KEY (`no`),
    UNIQUE KEY `ix_event_id` (`event_id`),
    UNIQUE KEY `ix_unique_event` (`aggregate_type`, `aggregate_id`, `aggregate_version`),
    KEY `$index` (`aggregate_type`,`aggregate_id`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        return [$statement];
    }

    public function columnNames(): array
    {
        return [
            'event_id',
            'event_name',
            'payload',
            'metadata',
            'created_at',
        ];
    }

    public function prepareData(\Iterator $streamEvents): array
    {
        $data = [];

        foreach ($streamEvents as $event) {
            $eventData = $this->messageConverter->convertToArray($event);

            $data[] = $eventData['uuid'];
            $data[] = $eventData['message_name'];
            $data[] = Json::encode($eventData['payload']);
            $data[] = Json::encode($eventData['metadata']);
            $data[] = $eventData['created_at']->format('Y-m-d\TH:i:s.u');
        }

        return $data;
    }

    public function generateTableName(StreamName $streamName): string
    {
        return $streamName->toString().'_event_stream';
    }

    public function indexName(): string
    {
        return 'ix_query_aggregate';
    }
}
