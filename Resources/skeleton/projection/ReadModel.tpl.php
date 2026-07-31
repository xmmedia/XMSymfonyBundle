<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class <?= $class_name; ?> extends AbstractReadModel
{
    protected const string TABLE = Table::<?= $model_upper; ?>;

    private const array TYPES = [];

    public function init(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `{$tableName}` (
  `<?= $id_field; ?>` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `<?= $name_field; ?>` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`<?= $id_field; ?>`),
  KEY `<?= $name_field; ?>` (`<?= $name_field; ?>`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        $this->connection->executeQuery($sql);
    }

    protected function insert(array $data): void
    {
        $this->connection->insert(self::TABLE, $data, self::TYPES);
    }

    protected function update(string $<?= $id_property; ?>, array $data): void
    {
        $this->connection->update(self::TABLE, $data, ['<?= $id_field; ?>' => $<?= $id_property; ?>], self::TYPES);
    }

    protected function remove(string $<?= $id_property; ?>): void
    {
        $this->connection->delete(self::TABLE, ['<?= $id_field; ?>' => $<?= $id_property; ?>]);
    }
}
