<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class <?= $class_name; ?> extends AbstractReadModel
{
    protected const TABLE = Table::<?= $model_upper; ?>;

    public function init(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `$tableName` (
  `<?= $id_field; ?>` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        $this->connection->executeQuery($sql);

        $sql = <<<EOT
ALTER TABLE `$tableName`
  ADD PRIMARY KEY (`<?= $id_field; ?>`),
  ADD KEY `name` (`name`) USING BTREE;
EOT;

        $this->connection->executeQuery($sql);
    }

    protected function insert(array $data, array $types = []): void
    {
        $this->connection->insert(self::TABLE, $data, $types);
    }

    protected function update(string $<?= $id_property; ?>, array $data, array $types = []): void
    {
        $this->connection->update(
            self::TABLE,
            $data,
            ['<?= $id_field; ?>' => $<?= $id_property; ?>],
            $types,
        );
    }

    protected function remove(string $<?= $id_property; ?>): void
    {
        $this->connection->delete(self::TABLE, ['<?= $id_field; ?>' => $<?= $id_property; ?>]);
    }
}
