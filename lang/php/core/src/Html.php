<?php

declare(strict_types=1);

namespace Otaqq\HtmlGraft;

/**
 * Surgical edits on an opaque HTML string, keyed by literal markers and tag names (see spec/README.md).
 *
 * Every method treats the HTML as text, never a DOM: markup is returned byte-for-byte except where an edit
 * applied, an edit that finds no target returns the input unchanged, and no edit adds a tag that was absent.
 * The methods are pure and static; {@see Graft} threads one string through a sequence of them.
 */
final class Html
{
    /**
     * Fill the first slot named $name (the region between `<!--@slot:NAME-->` and `<!--@/slot:NAME-->`).
     *
     * $inner as a string replaces the content (the markers are removed); an empty string empties the region.
     * $inner as null keeps the content as written and only removes the markers ("off").
     * An invalid name, or a missing/misordered open or close marker, returns the input unchanged.
     */
    public static function slot(string $html, string $name, ?string $inner): string
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $name) !== 1) {
            return $html;
        }

        $open = '<!--@slot:'.$name.'-->';
        $close = '<!--@/slot:'.$name.'-->';

        $openPos = strpos($html, $open);
        if ($openPos === false) {
            return $html;
        }
        $contentStart = $openPos + strlen($open);

        $closePos = strpos($html, $close, $contentStart);
        if ($closePos === false) {
            return $html;
        }

        $kept = $inner ?? substr($html, $contentStart, $closePos - $contentStart);

        return substr($html, 0, $openPos).$kept.substr($html, $closePos + strlen($close));
    }

    /**
     * Insert $insert immediately before the first case-insensitive occurrence of the literal $needle
     * (e.g. `</head>`). Returns the input unchanged if $needle does not occur.
     */
    public static function before(string $html, string $needle, string $insert): string
    {
        $pos = stripos($html, $needle);
        if ($pos === false) {
            return $html;
        }

        return substr($html, 0, $pos).$insert.substr($html, $pos);
    }

    /**
     * Insert $insert immediately after the first opening tag named $tag (`<tag ...>`, whatever its
     * attributes), case-insensitively. Returns the input unchanged if there is no such tag.
     */
    public static function afterOpenTag(string $html, string $tag, string $insert): string
    {
        $pattern = '/<'.preg_quote($tag, '/').'\b[^>]*>/i';

        return preg_replace_callback(
            $pattern,
            static fn (array $m): string => $m[0].$insert,
            $html,
            1,
        ) ?? $html;
    }

    /**
     * On every opening tag named $tag that carries $matchAttr="$matchValue" (either quote; the name
     * case-insensitive, the value literal), replace its $setAttr value with $value, HTML-escaped and in
     * double quotes. A matching tag without $setAttr is left unchanged (the attribute is never added); tags
     * without $matchAttr="$matchValue" are untouched.
     */
    public static function attr(
        string $html,
        string $tag,
        string $matchAttr,
        string $matchValue,
        string $setAttr,
        string $value,
    ): string {
        $tagPattern = '/<'.preg_quote($tag, '/').'\b[^>]*>/i';
        $matchPattern = '/\b'.preg_quote($matchAttr, '/').'=(["\'])'.preg_quote($matchValue, '/').'\1/i';
        $setPattern = '/\b'.preg_quote($setAttr, '/').'=(["\']).*?\1/i';
        $replacement = $setAttr.'="'.htmlspecialchars($value, ENT_QUOTES).'"';

        return preg_replace_callback($tagPattern, static function (array $m) use ($matchPattern, $setPattern, $replacement): string {
            if (preg_match($matchPattern, $m[0]) !== 1) {
                return $m[0];
            }

            return preg_replace_callback(
                $setPattern,
                static fn (): string => $replacement,
                $m[0],
                1,
            ) ?? $m[0];
        }, $html) ?? $html;
    }

    /**
     * Whether the literal string $marker occurs in $html.
     */
    public static function has(string $html, string $marker): bool
    {
        return $marker !== '' && str_contains($html, $marker);
    }

    /**
     * Remove every occurrence of the literal string $marker.
     */
    public static function strip(string $html, string $marker): string
    {
        return $marker === '' ? $html : str_replace($marker, '', $html);
    }
}
