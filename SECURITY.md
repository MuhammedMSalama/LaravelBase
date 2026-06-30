# Security Policy

## Supported versions

| Version | Supported |
|---|---|
| 3.x (current) | Full support: security fixes, bug fixes, new features |
| 2.x | Security fixes only |
| 1.x | End of life: no further updates |

---

## Reporting a vulnerability

**Do not open a public GitHub issue for a security vulnerability.**

Please report security vulnerabilities through one of the following private
channels:

- **GitHub private vulnerability reporting**: open a confidential report at
  <https://github.com/MuhammedMSalama/LaravelBase/security/advisories/new>.
- **Maintainer email**: contact Muhammed Salama at
  <devmuhammedsalama@gmail.com>.

These instructions match the security guidance in the README. Public issues,
pull requests, or discussions should not include vulnerability details until a
fix has been released.

Include as much detail as possible:

- A description of the vulnerability and its potential impact.
- Steps to reproduce or a proof-of-concept.
- The version or commit affected.
- Any relevant configuration, database driver, generated stub, or environment
  details.

---

## Response process

1. **Acknowledgement**: within 48 hours of receipt.
2. **Assessment**: the maintainer will triage severity and confirm whether the
   report is accepted.
3. **Fix**: a patch will be developed privately.
4. **Coordinated disclosure**: the fix will be released and a GitHub Security
   Advisory published. Credit will be given to the reporter unless anonymity is
   requested.

---

## Scope

The following are in scope:

- `src/`: all package source classes, traits, and helpers.
- `src/Console/Stubs/*.stub`: generated code that could introduce
  vulnerabilities into consuming applications.
- `config/base.php`: configuration handling.

The following are out of scope:

- Vulnerabilities in consuming applications that result from developers removing
  the package's security constraints.
- Issues in `require-dev` packages. Report those upstream.

---

## Security design notes

| Component | Decision |
|---|---|
| `AbstractFilter` | Only columns declared in `$filters` or `$sortable` are applied to the query. Unknown request parameters are ignored. |
| `ImageUploadTrait` | File extension is derived from the MIME type via `finfo`, not the client-supplied filename. |
| `CreateDatabaseCommand` | Database name, charset, collation, and PostgreSQL encoding are validated against `^[A-Za-z0-9_\-]+$` before SQL interpolation. |
| `BaseRequest` | `authorize()` returns `true` by default for scaffolding; production subclasses should override it with gate or policy checks. |
