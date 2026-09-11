<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Error;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use PHPUnit\Framework\Attributes\DataProvider;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\CodedUserError;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\LinkExpiredError;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\NotFoundError;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\TooManyRequestsError;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class CodedUserErrorTest extends BaseTestCase
{
    /**
     * @param class-string<CodedUserError> $class
     */
    #[DataProvider('errorProvider')]
    public function testCodeSent(string $class, string $code): void
    {
        $message = $this->faker()->sentence();
        $previous = new \Exception($this->faker()->sentence());

        $exception = new $class($message, $previous);

        $this->assertSame($code, $exception->errorCode());
        $this->assertSame($previous, $exception->getPrevious());

        $formatted = FormattedError::createFromException(new Error($message, previous: $exception));

        $this->assertSame($message, $formatted['message']);
        $this->assertSame(['code' => $code], $formatted['extensions']);
    }

    public static function errorProvider(): \Generator
    {
        yield 'not found' => [NotFoundError::class, 'NOT_FOUND'];
        yield 'link expired' => [LinkExpiredError::class, 'LINK_EXPIRED'];
        yield 'too many requests' => [TooManyRequestsError::class, 'TOO_MANY_REQUESTS'];
    }
}
