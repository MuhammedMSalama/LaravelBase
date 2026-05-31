# Changelog

All notable changes to `laravel-base` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
