# Conformance data

Shared inputs and expected results, so every language implementation behaves the same. Each file is plain
JSON; a test in each language loads it and checks its own code against it.

| File | What it pins |
|---|---|
| `graft.json` | Every operation: an array of `{op, args, html, expected}` cases. `op` is `slot`, `before`, `afterOpenTag`, `attr`, `has` or `strip`; `args` are the operation's arguments in order (the leading `html` argument is given separately as `html`); `expected` is the resulting HTML, or a boolean for `has`. A `null` in `args` is a real null (e.g. `slot` "off"). |
