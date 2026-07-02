<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\UuidInterface;
use Xm\SymfonyBundle\Model\ValueObject;

final readonly class <?= $class_name; ?> implements ValueObject, UuidInterface, \Stringable
{
    use UuidId;
}
