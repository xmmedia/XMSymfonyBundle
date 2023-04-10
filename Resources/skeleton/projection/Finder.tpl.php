<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $id_class; ?>;
use <?= $not_found_class; ?>;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method <?= $entity_class_short; ?>|null find(<?= $id_class_short; ?>|string $id, int|null $lockMode = null, int|null $lockVersion = null)
 * @method <?= $entity_class_short; ?>|null findOneBy(array $criteria, array $orderBy = null)
 * @method <?= $entity_class_short; ?>[] findAll()
 * @method <?= $entity_class_short; ?>[] findBy(array $criteria, array $orderBy = null, int|null $limit = null, int|null $offset = null)
 */
class <?= $class_name; ?> extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, <?= $entity_class_short; ?>::class);
    }

    public function findOrThrow(<?= $id_class_short; ?>|string $id): <?= $entity_class_short; ?><?= "\n"; ?>
    {
        $<?= $entity ?> = $this->find($id);
        if (!$<?= $entity ?>) {
            throw <?= $not_found_class_short; ?>::with<?= $id_class_short; ?>($id);
        }

        return $<?= $entity ?>;
    }

    public function findRefreshed(<?= $id_class_short; ?>|string $id): ?<?= $entity_class_short; ?><?= "\n"; ?>
    {
        $<?= $entity ?> = $this->find($id);

        if (!$<?= $entity ?>) {
            return null;
        }

        $this->getEntityManager()->refresh($<?= $entity ?>);

        return $<?= $entity ?>;
    }
}
