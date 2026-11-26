<?php

declare(strict_types=1);

namespace Otaqq\HtmlGraft\Tests;

use Otaqq\HtmlGraft\Html;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Runs every case in conformance/graft.json against the PHP core, so this implementation matches the shared
 * cross-language contract.
 */
final class ConformanceTest extends TestCase
{
    /**
     * @param  list<mixed>  $args
     */
    #[DataProvider('cases')]
    public function test_conformance_case(string $op, array $args, string $html, string|bool $expected): void
    {
        $result = match ($op) {
            'slot' => Html::slot($html, $args[0], $args[1]),
            'before' => Html::before($html, $args[0], $args[1]),
            'afterOpenTag' => Html::afterOpenTag($html, $args[0], $args[1]),
            'attr' => Html::attr($html, $args[0], $args[1], $args[2], $args[3], $args[4]),
            'has' => Html::has($html, $args[0]),
            'strip' => Html::strip($html, $args[0]),
        };

        $this->assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{string, list<mixed>, string, string|bool}>
     */
    public static function cases(): iterable
    {
        $path = __DIR__.'/../../../../conformance/graft.json';
        /** @var list<array{op: string, args: list<mixed>, html: string, expected: string|bool}> $cases */
        $cases = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        foreach ($cases as $i => $case) {
            yield $case['op'].' #'.$i => [$case['op'], $case['args'], $case['html'], $case['expected']];
        }
    }
}
