<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class EmailTest extends BaseTestCase
{
    public function testFromStrings(): void
    {
        $faker = $this->faker();

        $email = $faker->email();
        $name = $faker->name();

        $vo = Email::fromString($email, $name);

        $this->assertEquals($email, $vo->email());
        $this->assertEquals($email, $vo->toString());
        $this->assertEquals($email, (string) $vo);
        $this->assertEquals($name, $vo->name());
        $this->assertEquals(['email' => $email, 'name' => $name], $vo->toArray());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $email = $faker->email();
        $name = $faker->name();

        $vo = Email::fromArray(['email' => $email, 'name' => $name]);

        $this->assertEquals(['email' => $email, 'name' => $name], $vo->toArray());
    }

    public function testFromStringsWithoutName(): void
    {
        $faker = $this->faker();

        $email = $faker->email();

        $vo = Email::fromString($email);

        $this->assertEquals($email, $vo->toString());
        $this->assertEquals($email, (string) $vo);
        $this->assertNull($vo->name());
        $this->assertEquals($email, $vo->email());
        $this->assertEquals($email, $vo->withName());
        $this->assertEquals(['email' => $email, 'name' => null], $vo->toArray());
    }

    public function testNameTruncate(): void
    {
        $faker = $this->faker();

        $email = $faker->email();
        $name = trim($faker->words(100, true)); // over 100 characters long

        $vo = Email::fromString($email, $name);

        $this->assertEquals(trim(mb_substr($name, 0, 99)).'…', $vo->name());
        $this->assertEquals(trim(mb_substr($name, 0, 20)).' <'.$email.'>', $vo->withName());
    }

    public function testNullName(): void
    {
        $faker = $this->faker();

        $email = $faker->email();

        $vo = Email::fromString($email, null);

        $this->assertEquals($email, $vo->toString());
        $this->assertEquals($email, (string) $vo);
        $this->assertNull($vo->name());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Email::fromString('');
    }

    public function testInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Email::fromString('asdf');
    }

    public function testNameTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Email::fromString('Name Name Name Name Name Name Name Name Name Name Name Name Name');
    }

    public function testFromStringsWithNameMethod(): void
    {
        $vo = Email::fromString('email@email.com', 'Name');

        $this->assertEquals('Name <email@email.com>', $vo->withName());
    }

    public function testFromStringsWithNameMethodWithoutName(): void
    {
        $vo = Email::fromString('email@email.com');

        $this->assertEquals('email@email.com', $vo->withName());
    }

    public function testFromStringsWithNameMethodTooLong(): void
    {
        $vo = Email::fromString('email@email.com', 'Name Name Name Name Name Name Name Name');

        $this->assertEquals('Name Name Name Name <email@email.com>', $vo->withName());
    }

    public function testFromStringsWithNameMethodWithComma(): void
    {
        $vo = Email::fromString('email@email.com', 'Name, Name');

        $this->assertEquals('Name  Name <email@email.com>', $vo->withName());
    }

    public function testFromStringsWithNameMethodWithSemicolon(): void
    {
        $vo = Email::fromString('email@email.com', 'Name; Name');

        $this->assertEquals('Name  Name <email@email.com>', $vo->withName());
    }

    public function testFromStringsWithNameMethodWithCommaAndSemicolon(): void
    {
        $vo = Email::fromString('email@email.com', 'Name; Name, Name');

        $this->assertEquals('Name  Name  Name <email@email.com>', $vo->withName());
    }

    public function testObfuscated(): void
    {
        $vo = Email::fromString('email@email.com');

        $this->assertSame('em…@e….com', $vo->obfuscated());
    }

    public function testSameAs(): void
    {
        $vo1 = Email::fromString('email@email.com');
        $vo2 = Email::fromString('email@email.com');

        $this->assertTrue($vo1->sameValueAs($vo2));
    }

    public function testSameAsWithName(): void
    {
        $faker = $this->faker();

        $vo1 = Email::fromString('email@email.com', $faker->unique()->name());
        $vo2 = Email::fromString('email@email.com', $faker->unique()->name());

        $this->assertTrue($vo1->sameValueAs($vo2));
    }

    public function testSameAsCapitals(): void
    {
        $vo1 = Email::fromString('eMail@email.com');
        $vo2 = Email::fromString('email@eMail.com');

        $this->assertTrue($vo1->sameValueAs($vo2));
    }

    public function testSameAsDiffClass(): void
    {
        $vo = Email::fromString('eMail@email.com');

        $this->assertFalse($vo->sameValueAs(FakeVo::create()));
    }
}
