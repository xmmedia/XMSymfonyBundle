<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $id_class; ?>;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method <?= $entity_class_short; ?>|null find(<?= $id_class_short; ?>|string $id, $lockMode = null, $lockVersion = null)
 * @method <?= $entity_class_short; ?>|null findOneBy(array $criteria, array $orderBy = null)
 * @method <?= $entity_class_short; ?>[] findAll()
 * @method <?= $entity_class_short; ?>[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class <?= $class_name; ?> extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, <?= $entity_class_short; ?>::class);
    }

    /**
     * @param <?= $id_class_short; ?>|string $id
     */
    public function findRefreshed($id): ?<?= $entity_class_short; ?><?= "\n"; ?>
    {
        $<?= $entity ?> = $this->find($id);

        if (!$<?= $entity ?>) {
            return null;
        }

        $this->getEntityManager()->refresh($<?= $entity ?>);

        return $<?= $entity ?>;
    }
}
