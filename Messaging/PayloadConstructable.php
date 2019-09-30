<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messaging;

interface PayloadConstructable
{
    public function __construct(array $payload = []);
}
