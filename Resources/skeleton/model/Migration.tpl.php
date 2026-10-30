<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $migration_namespace; ?>;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Xm\SymfonyBundle\Migrations\ProjectionAwareMigration;

final class Version<?= date('Ymd'); ?><?= $migration_number; ?> extends AbstractMigration
{
    use ProjectionAwareMigration;

    #[\Override]
    public function getDescription(): string
    {
        return 'Create <?= $stream_name; ?> event stream, then run projection.';
    }

    public function up(Schema $schema): void
    {
        // noop
    }

    public function postUp(Schema $schema): void
    {
        $this->createEventStream('<?= $stream_name; ?>');
        $this->runProjection('<?= $projection_name; ?>_projection');
    }
}
