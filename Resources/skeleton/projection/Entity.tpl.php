<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="<?= $finder_class; ?>")
 */
class <?= $class_name; ?><?= "\n"; ?>
{
    /**
     * @var \Ramsey\Uuid\Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $<?= $id_property; ?>;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $name;

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromUuid($this-><?= $id_property; ?>);
    }

    public function name(): string
    {
        return $this->name;
    }
}
