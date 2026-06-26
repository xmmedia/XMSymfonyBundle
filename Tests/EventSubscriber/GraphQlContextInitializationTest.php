<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSubscriber;

use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Xm\SymfonyBundle\EventSubscriber\GraphQlContextInitialization;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class GraphQlContextInitializationTest extends BaseTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = GraphQlContextInitialization::getSubscribedEvents();

        $this->assertArrayHasKey(Events::PRE_EXECUTOR, $events);
        $this->assertEquals('onPreExecutor', $events[Events::PRE_EXECUTOR]);
    }

    public function testSubscriberImplementsInterface(): void
    {
        $request = Request::create('/graphql', 'POST');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $subscriber = new GraphQlContextInitialization($requestStack);

        $this->assertInstanceOf(\Symfony\Component\EventDispatcher\EventSubscriberInterface::class, $subscriber);
    }

    public function testOnPreExecutorMethodExists(): void
    {
        $requestStack = new RequestStack();
        $subscriber = new GraphQlContextInitialization($requestStack);

        $this->assertTrue(method_exists($subscriber, 'onPreExecutor'));
    }
}
