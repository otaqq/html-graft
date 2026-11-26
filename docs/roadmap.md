# Roadmap

## v0.1: PHP

- [x] Spec: slot (comment markers, string / null), before, afterOpenTag, attr (match + set, never adds), has, strip — all non-destructive, no DOM re-parse
- [x] Conformance data: one file of `{op, args, html, expected}` cases
- [x] PHP core: `Html` (pure operations) and `Graft` (fluent builder)
- [x] Tests on PHP 8.3, 8.4 and 8.5
- [ ] First user: replace an application's hand-written serve-time HTML rewriter
- [ ] Tag v0.1.0

## Languages

Each follows [spec/](../spec) and passes [conformance/](../conformance). Check that names are free before
publishing.

- [ ] PHP: Composer `otaqq/html-graft`
- [ ] Node.js: npm `html-graft` (TypeScript)
- [ ] Ruby: gem `html_graft`
- [ ] Python: PyPI `html-graft`
- [ ] Go: module `github.com/otaqq/html-graft/lang/go`

## Later

- A response post-processing adapter per framework (e.g. a Laravel macro / middleware that grafts outgoing
  HTML responses) — added once a name and a real need settle, so the core stays framework-free
- `after(needle, insert)` (literal, mirroring `before`) if a use beyond opening tags appears
- Fill every slot of a name, not only the first, as an opt-in
