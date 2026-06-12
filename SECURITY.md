# Security Policy

## Supported versions

| Version | Supported |
|---|---|
| 3.x (current) | Full support — security fixes, bug fixes, new features |
| 2.x | Security fixes only |
| 1.x | End of life — no further updates |

---

## Reporting a vulnerability

**Do not open a public GitHub issue for a security vulnerability.**

Please report security vulnerabilities through one of the following channels:

- **GitHub private vulnerability reporting** — on the repository page, go to
  _Security_ → _Report a vulnerability_ and submit the report privately.
- **Direct contact** — reach the maintainer at the contact details listed on the
  [author's GitHub profile](https://github.com/Muhammed2024Salama).

Include as much detail as possible:

- A description of the vulnerability and its potential impact.
- Steps to reproduce or a proof-of-concept.
- The version(s) of the package affected.
- Any relevant configuration or environment details.

---

## Response process

1. **Acknowledgement** — within 48 hours of receipt.
2. **Assessment** — the maintainer will triage severity and confirm whether the
   report is accepted.
3. **Fix** — a patch will be developed on a private branch.
4. **Coordinated disclosure** — the fix will be released and a GitHub Security
   Advisory published. Credit will be given to the reporter unless anonymity is
   requested.

---

## Scope

The following are in scope:

- `src/` — all package source classes, traits, and helpers.
- `src/Console/Stubs/*.stub` — generated code that could introduce
  vulnerabilities into consuming applications (e.g. SQL injection, XSS,
  command injection in generated stubs).
- `config/base.php` — configuration handling.

The following are **out of scope**:

- Vulnerabilities in consuming applications that result from developers
  removing the package's security constraints (e.g. disabling the `$filters`
  whitelist in `AbstractFilter`).
- Issues in `require-dev` packages (PHPStan, Orchestra Testbench, PHPUnit) —
  report those upstream.

---

## Security design notes

These hardening decisions are documented here for reviewers:

| Component | Decision |
|---|---|
| `AbstractFilter` | Only columns declared in `$filters` or `$sortable` are ever applied to the query. Unknown request parameters are silently ignored — the filter is safe against column-injection attacks by design. |
| `ImageUploadTrait` | File extension is derived from the MIME type via `finfo`, not the client-supplied filename, preventing extension-spoofing attacks (e.g. a PHP shell named `shell.php.jpg`). |
| `CreateDatabaseCommand` | Database name, charset, collation, and PostgreSQL encoding are validated against `^[A-Za-z0-9_\-]+$` before SQL interpolation. |
| `BaseRequest` | `authorize()` returns `true` by default — this is intentional scaffolding; production subclasses must override it with gate or policy checks. |
