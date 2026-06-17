# Contributing to Laravel Base

Contributions are welcome — bug fixes, improvements, new tests, and documentation
updates. Please read this guide before opening a pull request.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Prerequisites](#prerequisites)
- [Local setup](#local-setup)
- [Running tests](#running-tests)
- [Running static analysis](#running-static-analysis)
- [Issues and discussions](#issues-and-discussions)
- [Submitting a pull request](#submitting-a-pull-request)
- [Coding standards](#coding-standards)
- [Stub changes](#stub-changes)
- [Scope guidelines](#scope-guidelines)

---

## Code of Conduct

Please read and follow the [Code of Conduct](CODE_OF_CONDUCT.md). Be
respectful. Disagreement about code is fine; personal attacks are not.

---

## Issues and discussions

Use the GitHub issue templates when reporting bugs or proposing features. Bug
reports should include a minimal reproduction, package version, Laravel version,
PHP version, and relevant command output. Feature requests should describe the
Laravel workflow they improve and whether the proposal changes public behavior.

Security vulnerabilities must be reported privately through GitHub Security
Advisories or by email as described in [SECURITY.md](SECURITY.md).

---

## Prerequisites

| Tool | Version |
|---|---|
| PHP | 8.1 or above |
| Composer | 2.x |
| Git | any recent version |

No Docker or database server is required — tests run on an in-memory SQLite
database via Orchestra Testbench.

---

## Local setup

```bash
# 1. Fork the repository on GitHub, then clone your fork:
git clone https://github.com/<your-username>/LaravelBase.git
cd LaravelBase

# 2. Install dependencies:
composer install

# 3. Verify everything passes before you start:
composer test
composer analyse
```

---

## Running tests

```bash
composer test
```

This runs PHPUnit via Orchestra Testbench with an in-memory SQLite database.
All tests must pass before a PR can be merged.

To run a single test file:

```bash
vendor/bin/phpunit tests/Feature/MakeModuleCommandTest.php
```

To run a single test method:

```bash
vendor/bin/phpunit --filter test_generates_full_module
```

---

## Running static analysis

```bash
composer analyse
```

PHPStan level 5 with Larastan. The analysis must produce zero errors.

---

## Running the code style linter

```bash
# Auto-fix violations
composer pint

# CI-safe check (exit 1 if any violation found)
composer lint
```

Laravel Pint enforces the Laravel preset. Stubs under `src/Console/Stubs/` are
excluded because they contain template tokens (`{{ class }}`) that would confuse
the formatter.

---

## Submitting a pull request

1. **Create a feature branch** from `main`:
   ```bash
   git checkout -b feature/my-improvement
   ```

2. **Make your changes.** Write tests for any behavior change — new behavior
   without a test will not be merged.

3. **Ensure CI passes locally:**
   ```bash
   composer test && composer analyse && composer lint
   ```

4. **Commit with a descriptive message:**
   ```
   feat: add --interactive flag to make:module
   fix: prevent duplicate migration when --force is passed with --only=migration
   docs: correct AbstractFilter example in README
   ```

5. **Push and open a PR against `main`** with a clear description of what
   changed and why. Complete the pull request template, link related issues or
   discussions, and call out any generated stub changes.

6. **CI must be green** before merging. The workflow runs PHPUnit across
   PHP 8.1–8.4 × Laravel 10–13, PHPStan analysis, Laravel Pint code-style
   check, and a `make:module` smoke test that lint-checks every generated file.

---

## Coding standards

- PHP 8.1+ syntax; native types everywhere.
- `declare(strict_types=1)` at the top of every source file.
- No magic methods that obscure the public API.
- Stubs use `{{ placeholder }}` tokens — keep that convention.
- No external dependencies added without discussion (keep `require` minimal).

---

## Stub changes

When you modify or add a stub (`src/Console/Stubs/*.stub`):

1. Update the corresponding test in `tests/Feature/MakeModuleCommandTest.php`
   to assert the new or changed content is generated correctly.
2. Verify that the CI smoke-test job (`make-module-smoke` in
   `.github/workflows/ci.yml`) still produces valid, lint-clean PHP from the
   updated stub.
3. Document the change in `CHANGELOG.md` under `[Unreleased]`.

---

## Scope guidelines

| In scope | Out of scope |
|---|---|
| Bug fixes to existing commands, base classes, or traits | Adding a dependency to `require` without discussion |
| New `--no-*` / `--only` / `--except` options | Changing the JSON response envelope shape (breaking change) |
| Improving generated stub quality | Auto-generating routes or `RouteServiceProvider` entries |
| Adding test coverage | IDE plugin / language server integrations |
| Documentation and README improvements | Integrations with third-party packages not already in `suggest` |

If you are unsure whether a change is in scope, open an issue first to discuss.
