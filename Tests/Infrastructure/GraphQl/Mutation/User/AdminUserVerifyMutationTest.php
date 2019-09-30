<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Mutation\User;

use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User\AdminUserVerifyMutation;
use Xm\SymfonyBundle\Model\User\Command\VerifyUserByAdmin;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminUserVerifyMutationTest extends BaseTestCase
{
    public function testActivate(): void
    {
        $faker = $this->faker();

        $data = [
            'userId' => $faker->uuid,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(VerifyUserByAdmin::class))
            ->andReturn(new Envelope(new \stdClass()));

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserVerifyMutation(
            $commandBus
        ))($args);

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }
}
