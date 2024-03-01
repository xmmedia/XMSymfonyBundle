<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use Xm\SymfonyBundle\Util\Filters;

class <?= $class_name; ?> extends Filters
{
    /** @var string General query, queries text fields on <?= $projection_name; ?>. */
    public const Q = 'q';
    public const OFFSET = 'offset';
}
