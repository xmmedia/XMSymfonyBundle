<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSubscriber;

use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

readonly class GraphQlErrorSubscriber implements EventSubscriberInterface
{
    public const string ACCESS_DENIED_MESSAGE = 'Access denied to this field.';
    public const string UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const string FORBIDDEN = 'FORBIDDEN';

    public function __construct(
        private Security $security,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ERROR_FORMATTING => ['onGraphqlErrorFormat', -128],
        ];
    }

    /**
     * Sets extensions.code to UNAUTHENTICATED or FORBIDDEN when access is denied (depending on
     * whether they're signed in). Other codes come from the CodedUserError thrown.
     * The message of a "Cannot query field" (validation) error is hidden from users who aren't
     * signed in (except in debug): its "Did you mean …" suggestions reveal the schema, even with
     * introspection off.
     */
    public function onGraphqlErrorFormat(ErrorFormattingEvent $event): void
    {
        $message = $event->getError()->getMessage();

        if (str_starts_with($message, 'Cannot query field')) {
            if (!$this->debug && !$this->isLoggedIn()) {
                $event->getFormattedError()->offsetSet('message', self::ACCESS_DENIED_MESSAGE);
            }

            return;
        }

        if (self::ACCESS_DENIED_MESSAGE === $message) {
            $this->setCode($event, $this->isLoggedIn() ? self::FORBIDDEN : self::UNAUTHENTICATED);
        }
    }

    private function isLoggedIn(): bool
    {
        return $this->security->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
    }

    private function setCode(ErrorFormattingEvent $event, string $code): void
    {
        $formattedError = $event->getFormattedError();

        $extensions = [];
        if ($formattedError->offsetExists('extensions')) {
            $extensions = $formattedError->offsetGet('extensions');
        }
        $extensions['code'] = $code;

        $formattedError->offsetSet('extensions', $extensions);
    }
}
