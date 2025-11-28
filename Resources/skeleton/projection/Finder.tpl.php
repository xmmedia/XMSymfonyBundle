<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $id_class; ?>;
use <?= $not_found_class; ?>;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @extends ServiceEntityRepository<\<?php $entity_class; ?>>
 *
 * @method <?= $entity_class_short; ?>|null find(<?= $id_class_short; ?>|string $id, LockMode|int|null $lockMode = null, int|null $lockVersion = null)
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

    #[ArrayShape([<?= $entity_class_short; ?>::class])]
    public function findByFilters(<?= $filters_class_short; ?> $filters): array
    {
        $rsm = $this->createResultSetMappingBuilder('<?= $projection_name_first_letter; ?>');
        $select = $rsm->generateSelectClause();
        $queryParts = (new <?= $query_builder_class_short; ?>())->queryParts($filters);

        $sql = <<<Query
SELECT {$select}
FROM `<?= $projection_name; ?>` <?= $projection_name_first_letter; ?><?= "\n"; ?>
{$queryParts['join']}
WHERE {$queryParts['where']}
GROUP BY <?= $projection_name_first_letter; ?>.<?= $id_field; ?><?= "\n"; ?>
ORDER BY {$queryParts['order']}
LIMIT :offset, :maxResults
Query;

        if ($filters->applied(<?= $filters_class_short; ?>::OFFSET)) {
            $queryParts['parameters']['offset'] = (int) $filters->get(<?= $filters_class_short; ?>::OFFSET);
        } else {
            $queryParts['parameters']['offset'] = 0;
        }
        $queryParts['parameters']['maxResults'] = 30;

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($queryParts['parameters']);

        return $query->getResult();
    }

    /**
     * Retrieve the total count based on filters.
     */
    public function countByFilters(<?= $filters_class_short; ?> $filters): int
    {
        $queryParts = (new <?= $query_builder_class_short; ?>())->queryParts($filters);

        $sql = <<<Query
SELECT COUNT(DISTINCT <?= $projection_name_first_letter; ?>.<?= $id_field; ?>)
FROM `<?= $projection_name; ?>` <?= $projection_name_first_letter; ?><?= "\n"; ?>
{$queryParts['join']}
WHERE {$queryParts['where']}
Query;

        return (int) $this->getEntityManager()->getConnection()
            ->executeQuery(
                $sql,
                $queryParts['parameters'],
                $queryParts['parameterTypes'],
            )
            ->fetchNumeric()[0];
    }
}
