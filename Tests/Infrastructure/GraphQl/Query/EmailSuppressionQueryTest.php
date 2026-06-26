<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Query;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Query\EmailSuppressionQuery;
use Xm\SymfonyBundle\Infrastructure\Service\EmailSuppressionCheckerInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class EmailSuppressionQueryTest extends BaseTestCase
{
    public function testInvokeReturnsSuppressionData(): void
    {
        $email = 'test@example.com';
        $expectedResult = [
            'suppressed'  => true,
            'reason'      => 'Bounced',
            'dateAdded'   => '2023-01-15',
            'espUrl'      => 'https://postmarkapp.com/servers/12345/suppressions',
        ];

        $suppressionChecker = \Mockery::mock(EmailSuppressionCheckerInterface::class);
        $suppressionChecker->shouldReceive('check')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($email) {
                return $arg instanceof Email && $arg->toString() === $email;
            }))
            ->andReturn($expectedResult);

        $query = new EmailSuppressionQuery($suppressionChecker);
        $result = $query($email);

        $this->assertEquals($expectedResult, $result);
    }

    public function testInvokeReturnsNotSuppressed(): void
    {
        $email = 'active@example.com';
        $expectedResult = [
            'suppressed'  => false,
            'reason'      => null,
            'dateAdded'   => null,
            'espUrl'      => 'https://postmarkapp.com/servers/12345/suppressions',
        ];

        $suppressionChecker = \Mockery::mock(EmailSuppressionCheckerInterface::class);
        $suppressionChecker->shouldReceive('check')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($email) {
                return $arg instanceof Email && $arg->toString() === $email;
            }))
            ->andReturn($expectedResult);

        $query = new EmailSuppressionQuery($suppressionChecker);
        $result = $query($email);

        $this->assertEquals($expectedResult, $result);
    }
}
