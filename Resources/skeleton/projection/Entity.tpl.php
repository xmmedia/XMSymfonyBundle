<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use <?= $entity_finder_class; ?>;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: <?= $entity_filter_class_short; ?>::class, readOnly: true)]
class <?= $class_name; ?><?= "\n"; ?>
{
    /**
     * @var \Ramsey\Uuid\Uuid
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidInterface $<?= $id_property; ?>;

    #[ORM\Column(length: 50)]
    private string $<?= $name_property; ?>;

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromUuid($this-><?= $id_property; ?>);
    }

    public function <?= $name_property; ?>(): string
    {
        return $this-><?= $name_property; ?>;
    }
}
