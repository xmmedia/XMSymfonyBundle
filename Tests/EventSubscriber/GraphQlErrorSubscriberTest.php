<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSubscriber;

use GraphQL\Error\Error;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Xm\SymfonyBundle\EventSubscriber\GraphQlErrorSubscriber;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class GraphQlErrorSubscriberTest extends BaseTestCase
{
    public function testSubscribedEvents(): void
    {
        $this->assertSame(
            ['onGraphqlErrorFormat', -128],
            GraphQlErrorSubscriber::getSubscribedEvents()[Events::ERROR_FORMATTING],
        );
    }

    public function testCannotQueryFieldNotLoggedIn(): void
    {
        $event = $this->format(new Error('Cannot query field '.$this->faker()->sentence()), false);

        $this->assertSame(
            GraphQlErrorSubscriber::ACCESS_DENIED_MESSAGE,
            $event->getFormattedError()->offsetGet('message'),
        );
        $this->assertFalse($event->getFormattedError()->offsetExists('extensions'));
    }

    public function testCannotQueryFieldLoggedIn(): void
    {
        $event = $this->format(new Error('Cannot query field '.$this->faker()->sentence()), true);

        $this->assertFalse($event->getFormattedError()->offsetExists('message'));
        $this->assertFalse($event->getFormattedError()->offsetExists('extensions'));
    }

    public function testCannotQueryFieldDebug(): void
    {
        $event = $this->format(new Error('Cannot query field '.$this->faker()->sentence()), null, true);

        $this->assertFalse($event->getFormattedError()->offsetExists('message'));
        $this->assertFalse($event->getFormattedError()->offsetExists('extensions'));
    }

    public function testAccessDeniedNotLoggedIn(): void
    {
        $event = $this->format(new Error(GraphQlErrorSubscriber::ACCESS_DENIED_MESSAGE), false);

        $this->assertCode(GraphQlErrorSubscriber::UNAUTHENTICATED, $event);
    }

    public function testAccessDeniedLoggedIn(): void
    {
        $event = $this->format(new Error(GraphQlErrorSubscriber::ACCESS_DENIED_MESSAGE), true);

        $this->assertCode(GraphQlErrorSubscriber::FORBIDDEN, $event);
    }

    public function testOtherErrorIgnored(): void
    {
        $event = $this->format(new Error($this->faker()->sentence()), null);

        $this->assertFalse($event->getFormattedError()->offsetExists('extensions'));
    }

    public function testKeepsExistingExtensions(): void
    {
        $file = $this->faker()->filePath();
        $event = new ErrorFormattingEvent(
            new Error(GraphQlErrorSubscriber::ACCESS_DENIED_MESSAGE),
            ['extensions' => ['file' => $file]],
        );

        $this->subscriber(false)->onGraphqlErrorFormat($event);

        $this->assertSame(
            ['file' => $file, 'code' => GraphQlErrorSubscriber::UNAUTHENTICATED],
            $event->getFormattedError()->offsetGet('extensions'),
        );
    }

    /**
     * @param bool|null $loggedIn null when isGranted() shouldn't be called
     */
    private function format(Error $error, ?bool $loggedIn, bool $debug = false): ErrorFormattingEvent
    {
        $event = new ErrorFormattingEvent($error, []);

        $this->subscriber($loggedIn, $debug)->onGraphqlErrorFormat($event);

        return $event;
    }

    private function subscriber(?bool $loggedIn, bool $debug = false): GraphQlErrorSubscriber
    {
        $security = \Mockery::mock(Security::class);
        if (null === $loggedIn) {
            $security->shouldNotReceive('isGranted');
        } else {
            $security->shouldReceive('isGranted')
                ->once()
                ->with(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)
                ->andReturn($loggedIn);
        }

        return new GraphQlErrorSubscriber($security, $debug);
    }

    private function assertCode(string $code, ErrorFormattingEvent $event): void
    {
        $this->assertSame($code, $event->getFormattedError()->offsetGet('extensions')['code']);
    }
}
