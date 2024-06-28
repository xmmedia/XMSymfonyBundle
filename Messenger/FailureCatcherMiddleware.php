<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * From: https://medium.com/@gregurco.vlad/upgrade-symfony-to-4-3-messenger-component-changes-fabf8ef71ff3.
 */
class FailureCatcherMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $returnedEnvelope = $stack->next()->handle($envelope, $stack);
        } catch (HandlerFailedException $e) {
            if (method_exists($e, 'getWrappedExceptions')) {
                throw $e->getWrappedExceptions()[0];
            }

            // deprecated in Symfony 6.4
            throw $e->getNestedExceptions()[0];
        }

        return $returnedEnvelope;
    }
}
