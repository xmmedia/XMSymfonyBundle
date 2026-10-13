<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Util\ContentDisposition;

class ContentDispositionTest extends BaseTestCase
{
    #[DataProvider('filenameProvider')]
    public function testMake(string $filename, string $expected): void
    {
        $this->assertSame(
            $expected,
            ContentDisposition::make(HeaderUtils::DISPOSITION_INLINE, $filename),
        );
    }

    public static function filenameProvider(): \Generator
    {
        yield 'ascii' => [
            'Jane Smith - WHMIS Certificate.pdf',
            'inline; filename="Jane Smith - WHMIS Certificate.pdf"',
        ];
        yield 'accented' => [
            'Zoë Ångström - WHMIS Certificate.pdf',
            'inline; filename="Zoe Angstrom - WHMIS Certificate.pdf"; filename*=utf-8\'\'Zo%C3%AB%20%C3%85ngstr%C3%B6m%20-%20WHMIS%20Certificate.pdf',
        ];
        yield 'not transliterable' => [
            'Jane 😀 - WHMIS Certificate.pdf',
            'inline; filename="Jane _ - WHMIS Certificate.pdf"; filename*=utf-8\'\'Jane%20%F0%9F%98%80%20-%20WHMIS%20Certificate.pdf',
        ];
        yield 'slashes' => [
            'Jane/Smith - A\\B Certificate.pdf',
            'inline; filename="Jane-Smith - A-B Certificate.pdf"',
        ];
        yield 'windows reserved' => [
            'Safety: Level 1 * "Final" <A|B>?.pdf',
            'inline; filename="Safety- Level 1 - -Final- -A-B--.pdf"',
        ];
        yield 'control characters' => [
            "Jane\nSmith\t\x7f.pdf",
            'inline; filename=Jane-Smith--.pdf',
        ];
        yield 'percent' => [
            'Jane Smith - 100% Certificate.pdf',
            'inline; filename="Jane Smith - 100_ Certificate.pdf"; filename*=utf-8\'\'Jane%20Smith%20-%20100%25%20Certificate.pdf',
        ];
        yield 'apostrophe' => [
            "José D'Amico.pdf",
            'inline; filename="Jose D\'Amico.pdf"; filename*=utf-8\'\'Jos%C3%A9%20D%27Amico.pdf',
        ];
        yield 'whitespace only' => [
            '   ',
            'inline; filename="   "',
        ];
    }
}
