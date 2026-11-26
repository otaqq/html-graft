<?php

declare(strict_types=1);

namespace Otaqq\HtmlGraft\Tests;

use Otaqq\HtmlGraft\Html;
use PHPUnit\Framework\TestCase;

/**
 * Edge cases for the core operations, beyond the shared conformance data.
 */
final class HtmlTest extends TestCase
{
    public function test_slot_は最初のスロットだけを埋め二つ目を残す(): void
    {
        $html = '<!--@slot:x-->1<!--@/slot:x--><!--@slot:x-->2<!--@/slot:x-->';

        $this->assertSame('A<!--@slot:x-->2<!--@/slot:x-->', Html::slot($html, 'x', 'A'));
    }

    public function test_slot_はnullで中身を静的に保つ(): void
    {
        $html = '<div><!--@slot:latest-->static<!--@/slot:latest--></div>';

        $this->assertSame('<div>static</div>', Html::slot($html, 'latest', null));
    }

    public function test_slot_は閉じが開く前にあると変更しない(): void
    {
        $html = '<!--@/slot:x-->oops<!--@slot:x-->';

        $this->assertSame($html, Html::slot($html, 'x', 'A'));
    }

    public function test_slot_は不正な名前で変更しない(): void
    {
        $html = '<!--@slot:a.b-->x<!--@/slot:a.b-->';

        $this->assertSame($html, Html::slot($html, 'a.b', 'Y'));
    }

    public function test_slot_の挿入文字列に含まれるドル記号やバックスラッシュをそのまま入れる(): void
    {
        $html = '<!--@slot:x-->old<!--@/slot:x-->';
        $inner = '$1 \\0 price $5';

        $this->assertSame($inner, Html::slot($html, 'x', $inner));
    }

    public function test_before_は大文字小文字を無視して最初の一致の前に入れる(): void
    {
        $html = '<head><title>t</title></HEAD>';

        $this->assertSame('<head><title>t</title><meta></HEAD>', Html::before($html, '</head>', '<meta>'));
    }

    public function test_before_は見つからなければ変更しない(): void
    {
        $html = '<body>no head</body>';

        $this->assertSame($html, Html::before($html, '</head>', '<meta>'));
    }

    public function test_before_の挿入はリテラルでドル記号を解釈しない(): void
    {
        $this->assertSame('$2$1</head>', Html::before('</head>', '</head>', '$2$1'));
    }

    public function test_afterOpenTag_は属性付きの開始タグの直後に入れる(): void
    {
        $html = '<body class="x" data-y>hi</body>';

        $this->assertSame('<body class="x" data-y>INShi</body>', Html::afterOpenTag($html, 'body', 'INS'));
    }

    public function test_afterOpenTag_は最初のタグだけに入れる(): void
    {
        $html = '<li>a</li><li>b</li>';

        $this->assertSame('<li>Xa</li><li>b</li>', Html::afterOpenTag($html, 'li', 'X'));
    }

    public function test_afterOpenTag_はタグが無ければ変更しない(): void
    {
        $html = '<head></head>';

        $this->assertSame($html, Html::afterOpenTag($html, 'body', 'X'));
    }

    public function test_attr_は条件に合うタグの属性だけ書き換える(): void
    {
        $html = '<meta property="og:title" content="keep">'
            .'<meta property="og:image" content="old.png">';
        $expected = '<meta property="og:title" content="keep">'
            .'<meta property="og:image" content="new.png">';

        $this->assertSame($expected, Html::attr($html, 'meta', 'property', 'og:image', 'content', 'new.png'));
    }

    public function test_attr_は対象属性が無ければ追加しない(): void
    {
        $html = '<meta property="og:image">';

        $this->assertSame($html, Html::attr($html, 'meta', 'property', 'og:image', 'content', 'new.png'));
    }

    public function test_attr_は値をhtmlエスケープする(): void
    {
        $html = "<meta property='og:image' content=\"old\">";
        $expected = '<meta property=\'og:image\' content="a&quot;&amp;&lt;b">';

        $this->assertSame($expected, Html::attr($html, 'meta', 'property', 'og:image', 'content', 'a"&<b'));
    }

    public function test_has_とstrip(): void
    {
        $html = '<x><!--m--><!--m-->';

        $this->assertTrue(Html::has($html, '<!--m-->'));
        $this->assertFalse(Html::has($html, '<!--n-->'));
        $this->assertSame('<x>', Html::strip($html, '<!--m-->'));
    }

    public function test_空のmarkerはhasで偽strip無変更(): void
    {
        $this->assertFalse(Html::has('<x>', ''));
        $this->assertSame('<x>', Html::strip('<x>', ''));
    }
}
