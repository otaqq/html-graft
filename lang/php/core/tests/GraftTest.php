<?php

declare(strict_types=1);

namespace Otaqq\HtmlGraft\Tests;

use Otaqq\HtmlGraft\Graft;
use PHPUnit\Framework\TestCase;

/**
 * The fluent builder threads one string through the operations and reads it back.
 */
final class GraftTest extends TestCase
{
    public function test_一連の操作を順に適用する(): void
    {
        $html = '<!--@top-->'
            .'<head><meta property="og:image" content="brand.png"></head>'
            .'<body><!--@slot:latest-->static<!--@/slot:latest--></body>';

        $out = Graft::make($html)
            ->strip('<!--@top-->')
            ->before('</head>', '<script>gtm</script>')
            ->afterOpenTag('body', '<noscript>gtm</noscript>')
            ->attr('meta', 'property', 'og:image', 'content', 'https://example.test/x.png')
            ->slot('latest', '<ul><li>job</li></ul>')
            ->html();

        $expected = '<head><meta property="og:image" content="https://example.test/x.png"><script>gtm</script></head>'
            .'<body><noscript>gtm</noscript><ul><li>job</li></ul></body>';

        $this->assertSame($expected, $out);
    }

    public function test_slotにnullを渡すと静的のまま(): void
    {
        $html = '<!--@slot:x-->keep<!--@/slot:x-->';

        $this->assertSame('keep', Graft::make($html)->slot('x', null)->html());
    }

    public function test_hasは文字列を変えずに判定する(): void
    {
        $graft = Graft::make('<html><!--@top--><body>');

        $this->assertTrue($graft->has('<!--@top-->'));
        $this->assertSame('<html><!--@top--><body>', $graft->html());
    }

    public function test_文字列化できる(): void
    {
        $this->assertSame('<p>x</p>', (string) Graft::make('<p>x</p>'));
    }
}
