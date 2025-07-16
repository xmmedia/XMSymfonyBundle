<?php

declare(strict_types=1);

namespace Infrastructure\GraphQl\Type;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\DateType;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class DateTypeTest extends BaseTestCase
{

    public function testParseValue(): void
    {
        $date = $this->faker()->date('Y-m-d');

        $type = new DateType();
        $result = $type->parseValue($date);

        $this->assertEquals($date, $result->format('Y-m-d'));
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testParseValueEmpty($empty): void
    {
        $type = new DateType();
        $this->assertNull($type->parseValue($empty));
    }

    public function emptyProvider(): array
    {
        return [
            [null],
            [''],
            [' '],
        ];
    }

    /**
     * Nearly impossible to test that PHP can't prase the date
     * because the regex ensures the format is correct before parsing.
     */
    public function testParseValueInvalidFormat(): void
    {
        $type = new DateType();

        $this->expectException(\Overblog\GraphQLBundle\Error\UserError::class);
        $this->expectExceptionMessage('The date is not in the format of YYYY-MM-DD. Received: "2023-13-1"');

        $type->parseValue('2023-13-1');
    }
}
