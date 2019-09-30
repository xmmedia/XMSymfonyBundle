<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements AggregateException
{
}
