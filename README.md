# html-graft

Rewrite an **opaque HTML document at serve time** — without re-parsing it into a DOM. Fill comment-marked
slots, insert fragments before a tag or right after an opening tag, and rewrite named `meta`/`link`
attributes. It is the nginx `sub_filter` / Server-Side-Includes idea, as a small, dependency-free library.

PHP is the first implementation. The [spec](spec) and the [conformance](conformance) data are
language-neutral, so other languages follow the same contract (see the [roadmap](docs/roadmap.md)).

Reach for it when a page's HTML is owned by someone else — a designer's hand-written page, an exported
template — and the app must graft a few things in at response time (an analytics tag, the current canonical
URL, a dynamic list) while leaving the rest **byte-for-byte**. A DOM library would re-serialize and subtly
rewrite the whole document; html-graft only touches what each operation targets, and anything it does not
find it leaves unchanged.

```
composer require otaqq/html-graft
```

Requires PHP 8.3+. No extensions, no framework.

## Usage

```php
use Otaqq\HtmlGraft\Graft;

$out = Graft::make($rawHtml)
    ->strip('<!--@render-->')                                  // drop the "process me" sentinel
    ->before('</head>', $analyticsHead)                        // inject into <head>
    ->afterOpenTag('body', $analyticsBody)                     // inject right after <body>
    ->attr('meta', 'property', 'og:image', 'content', $image)  // point hard-coded tags at this environment
    ->attr('link', 'rel', 'canonical', 'href', $url)
    ->slot('latest', $enabled ? $latestHtml : null)            // string = replace, null = keep static
    ->html();
```

The same operations are available as pure static functions on `Otaqq\HtmlGraft\Html` if you prefer them
without the builder.

### Markers

A slot is a pair of HTML comments the author leaves in the page:

```html
<!--@slot:latest-->
  <!-- the designer's placeholder; shown as-is when the slot is "off" (null) -->
<!--@/slot:latest-->
```

`slot('latest', $html)` swaps the placeholder for `$html`; `slot('latest', null)` removes the markers and
keeps the placeholder. Because the markers are HTML comments, the raw page is still a valid, previewable
document on its own.

## Operations

See [spec/](spec) for the exact contract and [conformance/](conformance) for the shared test cases.

| Operation | Does |
|---|---|
| `slot(name, inner)` | Fill the first `<!--@slot:NAME-->…<!--@/slot:NAME-->` (`string` replaces, `null` keeps static) |
| `before(needle, insert)` | Insert before the first case-insensitive occurrence of a literal string |
| `afterOpenTag(tag, insert)` | Insert right after the first `<tag …>` opening tag |
| `attr(tag, matchAttr, matchValue, setAttr, value)` | Rewrite `setAttr` on tags identified by `matchAttr="matchValue"` (never adds it) |
| `has(marker)` / `strip(marker)` | Test for / remove a literal marker |

Every operation returns the input unchanged when it finds no target.

## Development

```
mise run test        # PHP tests in Docker (nothing but Docker needed on the host)
```

## License

MIT
