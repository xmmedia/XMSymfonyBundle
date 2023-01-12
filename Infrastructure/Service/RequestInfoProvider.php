<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestInfoProvider
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function currentRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    public function userAgent(): ?string
    {
        return $this->currentRequest()->headers->get('User-Agent');
    }

    public function ipAddress(): ?string
    {
        return $this->currentRequest()->getClientIp();
    }
}
