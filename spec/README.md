# Spec

What every language implementation of html-graft does. The shared data in [conformance/](../conformance)
pins the examples.

html-graft rewrites an **opaque HTML string** at serve time. It never parses the document into a DOM and
re-serializes it, so markup the author wrote by hand (a designer's page, an exported template) comes back
byte-for-byte except where an operation changed it. Every operation that finds no target returns the input
unchanged; none ever adds a tag that was not there.

The operations are the nginx `sub_filter` / Server-Side-Includes idea: small, surgical edits keyed by
literal markers and tag names.

## slot

A slot is a region delimited by two HTML comments: `<!--@slot:NAME-->` (open) and `<!--@/slot:NAME-->`
(close). `NAME` matches `^[A-Za-z0-9_-]+$`.

`slot(html, name, inner)` acts on the **first** open/close pair of that name:

- `inner` is a string → the content between the markers is replaced with `inner` (the markers are removed).
  An empty string is a valid replacement (the region becomes empty).
- `inner` is null → the markers are removed and the content between them is kept as written ("off": the
  author's static content stays).
- `name` is invalid, or the open marker, the close marker, or a close-after-open ordering is missing → the
  input is returned unchanged.

Only the first pair is touched; a second region of the same name is left for a second call.

## before

`before(html, needle, insert)` inserts `insert` immediately before the **first case-insensitive** occurrence
of the literal string `needle` (e.g. `</head>`). If `needle` does not occur, the input is returned unchanged.
`needle` is matched literally, not as a pattern.

## afterOpenTag

`afterOpenTag(html, tag, insert)` inserts `insert` immediately after the **first** opening tag named `tag`
(e.g. `body`), whatever attributes it carries. An opening tag is `<tag` followed by a word boundary, up to
the next `>`. The match is case-insensitive. If there is no such tag, the input is returned unchanged.

## attr

`attr(html, tag, matchAttr, matchValue, setAttr, value)` rewrites an attribute on the opening tags that
identify themselves by another attribute. For **every** opening tag named `tag` (case-insensitive) that
carries `matchAttr="matchValue"` (either single or double quotes; the attribute name is matched
case-insensitively and the value literally):

- if the tag also has `setAttr="..."`, its value is replaced with `value`, HTML-escaped (quotes included)
  and written in double quotes;
- if the tag has no `setAttr`, that tag is left unchanged (the attribute is never added).

Tags that do not carry `matchAttr="matchValue"` are untouched. Typical use: point a page's hard-coded
`og:image`, `og:url` and `canonical` at the current environment.

## has / strip

`has(html, marker)` is true when the literal string `marker` occurs in `html`.

`strip(html, marker)` removes **every** occurrence of the literal string `marker`. Used to drop a page's
"process me" sentinel comment once it has been handled.

## Builder

A small fluent builder threads one string through a sequence of the operations above
(`make(html).before(...).slot(...).html()`), each step feeding the next. It adds no behaviour of its own.
