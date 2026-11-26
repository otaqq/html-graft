<?php

declare(strict_types=1);

namespace Otaqq\HtmlGraft;

/**
 * Fluent builder that threads one HTML string through a sequence of {@see Html} operations.
 *
 *   $out = Graft::make($rawHtml)
 *       ->strip('<!--@front-top-->')
 *       ->before('</head>', $gtmHead)
 *       ->afterOpenTag('body', $gtmBody)
 *       ->attr('meta', 'property', 'og:image', 'content', $imageUrl)
 *       ->slot('latest', $enabled ? $latestHtml : null)
 *       ->html();
 *
 * Each step rewrites the held string and returns the same builder; the builder adds no behaviour of its own.
 */
final class Graft
{
    private function __construct(private string $html) {}

    /**
     * Start from an HTML string.
     */
    public static function make(string $html): self
    {
        return new self($html);
    }

    /**
     * {@see Html::slot()}
     */
    public function slot(string $name, ?string $inner): self
    {
        $this->html = Html::slot($this->html, $name, $inner);

        return $this;
    }

    /**
     * {@see Html::before()}
     */
    public function before(string $needle, string $insert): self
    {
        $this->html = Html::before($this->html, $needle, $insert);

        return $this;
    }

    /**
     * {@see Html::afterOpenTag()}
     */
    public function afterOpenTag(string $tag, string $insert): self
    {
        $this->html = Html::afterOpenTag($this->html, $tag, $insert);

        return $this;
    }

    /**
     * {@see Html::attr()}
     */
    public function attr(string $tag, string $matchAttr, string $matchValue, string $setAttr, string $value): self
    {
        $this->html = Html::attr($this->html, $tag, $matchAttr, $matchValue, $setAttr, $value);

        return $this;
    }

    /**
     * {@see Html::strip()}
     */
    public function strip(string $marker): self
    {
        $this->html = Html::strip($this->html, $marker);

        return $this;
    }

    /**
     * Whether the literal $marker occurs in the current HTML ({@see Html::has()}). Does not change the string.
     */
    public function has(string $marker): bool
    {
        return Html::has($this->html, $marker);
    }

    /**
     * The rewritten HTML.
     */
    public function html(): string
    {
        return $this->html;
    }

    public function __toString(): string
    {
        return $this->html;
    }
}
