<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore\PersistenceStrategy;

use Prooph\EventStore\Pdo\HasQueryHint;

/**
 * StreamStrategy with the USE INDEX query hint, for the event store used by
 * aggregate repositories, where loads filter on aggregate type & ID.
 */
final readonly class HintedStreamStrategy extends StreamStrategy implements HasQueryHint
{
}
