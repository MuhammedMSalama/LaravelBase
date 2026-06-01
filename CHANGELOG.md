# Changelog

All notable changes to `laravel-base` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.2.0] - 2026-06-02

### Added
- `make:repository` now generates the Eloquent model automatically when it does not
  exist (`app/Models/{Model}.php`). The generated model has `protected $guarded = []`
  with a TODO comment so `create()`/`update()` work out of the box. The model is
  **never overwritten** regardless of `--force`; pass `--no-model` to skip generation
  entirely.
- `--no-model` option: skip Eloquent model generation.
- `--provider` option: create (or update in place) `app/Providers/RepositoryServiceProvider.php`
  and insert an explicit `$this->app->bind(…)` for the generated interface/repository pair.
  - The provider is generated once and never overwritten.
  - Bindings are idempotent — re-running for the same name will not duplicate entries.
  - New bindings are inserted above a persistent `// {{ bindings }}` marker so the
    insertion point is always predictable.
  - The command prints the exact line to add to `bootstrap/providers.php` (Laravel 11+)
    or `config/app.php` (Laravel 10).
  - When both `auto_bind` and `--provider` are active the bindings are identical and
    harmless; set `auto_bind => false` in `config/base.php` to make the provider the
    sole binding mechanism.
- New stubs: `model.stub`, `repository-service-provider.stub`.
- 10 new tests covering model creation, model preservation, `--no-model` skip,
  `--force` not overwriting the model, provider creation, binding append, no-duplicate
  guarantee, existing provider preservation, and `--provider`-absent guard.

## [2.1.0] - 2026-06-02

### Added
- Laravel 13 compatibility (Orchestra Testbench 11, PHP ^8.3 for the L13 CI jobs).
  - `illuminate/*` constraints were already open-ended (`>=10.0`) and continue to accept Laravel 13.
  - `orchestra/testbench` updated to `^8.0|^9.0|^10.0|^11.0`.
  - `phpunit/phpunit` updated to `^10.5|^11.0|^12.0` to unblock Testbench 9/10/11's
    preference for PHPUnit 12.
  - CI matrix gains a `13.*` Laravel axis (PHP 8.3 and 8.4 only; PHP 8.1/8.2 excluded
    because Testbench 11 requires `^8.3`).

### Fixed
- CI badge in README was pointing to the wrong GitHub repository slug (`laravel-base` →
  `LaravelBase`); both the `<a href>` and the `<img src>` have been corrected.

## [2.0.0] - 2026-06-01

### Breaking changes
- `RepositoryInterface` and `ServiceInterface` methods now carry native PHP return types and typed
  parameters (`int|string $id`, `mixed $value`, `Collection`, `LengthAwarePaginator`, `Model`,
  `?Model`, `Builder`). Any class that implements these interfaces or overrides methods in
  `BaseRepository` / `BaseService` must add matching return-type declarations.

### Security
- `CreateDatabaseCommand`: database name, charset, collation, and PostgreSQL encoding are now
  validated against a strict allowlist (`^[A-Za-z0-9_\-]+$`) before being interpolated into SQL.
  Previously only the quoting character was stripped, leaving charset/collation unguarded.
- `ImageUploadTrait` (src + stubs): replaced `getClientOriginalExtension()` with `extension()`.
  The new method derives the file extension from the actual MIME type via `finfo`, preventing
  extension-spoofing attacks (e.g. a PHP shell named `shell.php.jpg`).
- `controller.plain.stub`: `$request->all()` replaced with `$request->except(['_token', '_method'])`
  and a prominent `TODO` comment directing developers to add validation rules.

### Fixed
- Published `ApiResponseTrait` stub was missing `unauthorized()` (401) and `forbidden()` (403)
  methods that exist in the package-side trait; callers using the published copy would get a fatal
  error at runtime.
- `BaseRepository::update()` issued a redundant `->fresh()` SELECT after every update. Eloquent
  already syncs model attributes in memory after `update()`; the extra query has been removed.
- Removed a misleading PHPDoc comment in `BaseRepository` describing an optional `model(): string`
  pattern the class does not actually support.
- `ServiceInterface::find()` PHPDoc now correctly states that the method throws
  `ModelNotFoundException` (it calls `findOrFail`, not `find`).

### Added
- Native return types and typed parameters across `RepositoryInterface`, `ServiceInterface`,
  `BaseRepository`, and `BaseService`.
- Test suite expanded from 7 to 27 tests (60 assertions):
  - `BaseRequestTest`: `authorize()` default, `failedValidation()` produces 422 envelope, error
    messages contain field-level detail.
  - `BaseServiceTest`: `store`, `find`, `all`, `update`, `destroy`, `repository()` accessor,
    `paginate`.
  - `MakeRepositoryCommandTest`: file generation, skipping existing files, `--force` overwrite,
    `--controller` custom name.
  - `CreateDatabaseCommandTest`: unconfigured connection, unsupported driver, empty database name,
    invalid database name characters, invalid charset characters.
- `phpunit.xml` added to `.gitignore` so it no longer shadows `phpunit.xml.dist`.

## [1.3.0] - 2026-06-01
### Added
- `make:repository` now also generates `Store{Name}Request` and `Update{Name}Request` Form Requests and wires them into the controller.
- `make:repository` now generates a `create_{table}_table` migration (skips if one already exists).
- New `base:create-database` command that creates the configured database if missing, supporting MySQL and PostgreSQL.
- Test suite (PHPUnit + Orchestra Testbench), PHPStan/Larastan static analysis, and GitHub Actions CI workflows.

## [1.2.0]
### Added
- Controller generation in `make:repository`, with an optional custom name.
- Publishable copies of the `ApiResponse` helper and the traits (`base-helpers`, `base-traits` tags).

## [1.1.0]
### Added
- `make:repository` command to scaffold the interface, repository, and service.
- Convention-based auto-binding of repository interfaces to implementations.

## [1.0.0]
### Added
- Initial release: `RepositoryInterface`/`ServiceInterface` contracts, `BaseRepository`, `BaseService`, `BaseRequest`, the `ApiResponse` helper, and the `ApiResponseTrait` and `ImageUploadTrait` traits.
