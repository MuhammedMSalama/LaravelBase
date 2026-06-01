<h1 align="center">Laravel Base</h1>

<p align="center">
  <strong>Repository–Service scaffolding, consistent API responses, and image handling for Laravel 10+.</strong><br>
  Stop rewriting boilerplate — extend a small set of tested base classes and focus on business logic.
</p>

<p align="center">
  <a href="https://packagist.org/packages/muhammedsalama/laravel-base">
    <img src="https://img.shields.io/packagist/v/muhammedsalama/laravel-base?style=flat-square" alt="Latest Version on Packagist">
  </a>
  <a href="https://packagist.org/packages/muhammedsalama/laravel-base">
    <img src="https://img.shields.io/packagist/dt/muhammedsalama/laravel-base?style=flat-square" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/muhammedsalama/laravel-base">
    <img src="https://img.shields.io/packagist/php-v/muhammedsalama/laravel-base?style=flat-square" alt="PHP Version">
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-green?style=flat-square" alt="License: MIT">
  </a>
  <a href="https://github.com/Muhammed2024Salama/LaravelBase/actions">
    <img src="https://img.shields.io/github/actions/workflow/status/Muhammed2024Salama/LaravelBase/ci.yml?branch=main&style=flat-square&label=CI" alt="CI">
  </a>
</p>

---

## Table of Contents

- [Why use this?](#why-use-this)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Architecture overview](#architecture-overview)
- [Usage](#usage)
  - [1. Generator command](#1-generator-command-make-repository)
  - [2. Manual setup](#2-manual-setup)
    - [Interface](#interface)
    - [Repository](#repository)
    - [Service](#service)
    - [Form Requests](#form-requests)
    - [Controller](#controller)
- [API Reference](#api-reference)
  - [RepositoryInterface](#repositoryinterface)
  - [BaseRepository](#baserepository)
  - [ServiceInterface](#serviceinterface)
  - [BaseService](#baseservice)
  - [BaseRequest](#baserequest)
  - [ApiResponse](#apiresponse)
  - [ApiResponseTrait](#apiresponsetrait)
  - [ImageUploadTrait](#imageuploadtrait)
- [Commands](#commands)
  - [make:repository](#makerepository)
  - [base:create-database](#basecreate-database)
- [Configuration](#configuration)
- [Publishing helpers and traits](#publishing-helpers-and-traits)
- [Testing and static analysis](#testing-and-static-analysis)
- [Troubleshooting](#troubleshooting)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)
- [Author](#author)

---

## Why use this?

Every Laravel API project ends up writing the same base classes: a `BaseRepository` with CRUD methods, a `BaseService` that wraps it, a `FormRequest` that returns JSON errors instead of redirecting, and a handful of response-shape helpers. This package ships all of that once, tested and type-safe, so you extend rather than copy-paste.

---

## Features

| Feature | Details |
|---|---|
| **Repository pattern** | `BaseRepository` with `all`, `paginate`, `find`, `findOrFail`, `findBy`, `create`, `update`, `delete`, and `query` |
| **Service layer** | `BaseService` that wraps any repository and keeps controllers thin |
| **Contracts-first design** | `RepositoryInterface` and `ServiceInterface` with full native type hints |
| **External validation** | `BaseRequest` (Form Request) moves validation out of controllers and returns a standard `422` JSON envelope on failure |
| **Consistent API responses** | Static `ApiResponse` helper and an `ApiResponseTrait` for controllers — identical JSON shape |
| **Image handling** | Upload, multi-upload, update (auto-deletes the old file), and delete via `ImageUploadTrait` |
| **Scaffold generator** | `make:repository` creates Interface, Repository, Service, Form Requests, Controller, and migration in one command |
| **Database creator** | `base:create-database` creates the configured MySQL or PostgreSQL database if it does not exist |
| **Auto-binding** | Interfaces are automatically bound to their repositories by naming convention — no manual registration needed |
| **Laravel 10/11/12/13, PHP 8.1+** | No upper-bound constraint on the Laravel version |

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.1` |
| Laravel | `10.x` and above (10 / 11 / 12 / 13) |

---

## Installation

### Via Packagist (recommended)

```bash
composer require muhammedsalama/laravel-base
```

The service provider is registered automatically through Laravel's package auto-discovery. No manual configuration is needed to get started.

<details>
<summary>Install directly from GitHub (VCS repository)</summary>

Add the repository entry to your project's `composer.json`, then require the package:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/Muhammed2024Salama/LaravelBase"
    }
]
```

```bash
composer require muhammedsalama/laravel-base:^1.0
```

</details>

<details>
<summary>Install from a local path (package development)</summary>

```json
"repositories": [
    {
        "type": "path",
        "url": "../laravel-base"
    }
],
"require": {
    "muhammedsalama/laravel-base": "*"
}
```

```bash
composer require muhammedsalama/laravel-base
```

</details>

---

## Quick Start

Scaffold a fully-wired `Product` resource in one command:

```bash
php artisan make:repository Product
```

That's it. The command creates:

```
app/
  Interfaces/ProductRepositoryInterface.php
  Repositories/ProductRepository.php
  Services/ProductService.php
  Http/
    Requests/Product/
      StoreProductRequest.php
      UpdateProductRequest.php
    Controllers/ProductController.php
database/migrations/xxxx_xx_xx_create_products_table.php
```

Register the resource routes, add your `$fillable` to the `Product` model, and you have a working CRUD API with consistent JSON responses and request validation — no other steps required (auto-binding wires the interface to the repository automatically).

---

## Architecture overview

```
HTTP Request
    │
    ▼
Controller          (thin — delegates everything)
    │  uses ApiResponseTrait
    │  type-hints Form Request
    ▼
Service             (business logic lives here)
    │  extends BaseService
    │  depends on RepositoryInterface
    ▼
Repository          (data access)
    │  extends BaseRepository
    │  wraps an Eloquent Model
    ▼
Database
```

Validation failures are caught by `BaseRequest::failedValidation()` before the controller is ever called, and the response is returned in the standard JSON envelope automatically.

---

## Usage

### 1. Generator command: `make:repository`

See the full [Commands → make:repository](#makerepository) reference below. A quick example:

```bash
# Full set: interface, repository, service, requests, controller, migration
php artisan make:repository Product

# Wrap a differently-named model
php artisan make:repository Post --model=Article

# Custom controller name; skip migration
php artisan make:repository Invoice --controller=InvoiceApiController --no-migration
```

### 2. Manual setup

The following shows how to wire a `Product` resource by hand.

#### Interface

```php
namespace App\Interfaces;

use MuhammedSalama\Base\Interfaces\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    // Add product-specific query methods here when needed.
}
```

#### Repository

```php
namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use MuhammedSalama\Base\Repositories\BaseRepository;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}
```

#### Service

```php
namespace App\Services;

use App\Interfaces\ProductRepositoryInterface;
use MuhammedSalama\Base\Services\BaseService;

class ProductService extends BaseService
{
    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
```

#### Form Requests

Validation lives in its own classes. Extend `BaseRequest`, which automatically returns the standard `422` envelope on failure — no extra code needed.

```php
namespace App\Http\Requests\Product;

use MuhammedSalama\Base\Requests\BaseRequest;

class StoreProductRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
```

```php
namespace App\Http\Requests\Product;

use MuhammedSalama\Base\Requests\BaseRequest;

class UpdateProductRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'  => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
```

A failed validation response:

```json
{
    "status": false,
    "message": "Validation error",
    "errors": {
        "name": ["The name field is required."]
    }
}
```

#### Controller

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;
use MuhammedSalama\Base\Traits\ApiResponseTrait;
use MuhammedSalama\Base\Traits\ImageUploadTrait;

class ProductController extends Controller
{
    use ApiResponseTrait, ImageUploadTrait;

    public function __construct(private ProductService $service) {}

    public function index()
    {
        return $this->paginated($this->service->paginate(15));
    }

    public function show(int $id)
    {
        return $this->success($this->service->find($id));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['image'] = $this->uploadImage($request, 'image', 'uploads/products');

        return $this->created($this->service->store($data));
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        return $this->success($this->service->update($id, $request->validated()));
    }

    public function destroy(int $id)
    {
        $this->service->destroy($id);

        return $this->success(null, 'Deleted successfully');
    }
}
```

---

## API Reference

### RepositoryInterface

`MuhammedSalama\Base\Interfaces\RepositoryInterface`

| Method | Signature | Returns | Notes |
|---|---|---|---|
| `all` | `all(array $columns, array $relations): Collection` | `Collection<int, Model>` | Eager-loads `$relations` |
| `paginate` | `paginate(int $perPage, array $columns, array $relations): LengthAwarePaginator` | `LengthAwarePaginator` | |
| `find` | `find(int\|string $id, array $columns, array $relations): ?Model` | `Model\|null` | Returns `null` when not found |
| `findOrFail` | `findOrFail(int\|string $id, array $columns, array $relations): Model` | `Model` | Throws `ModelNotFoundException` |
| `findBy` | `findBy(string $column, mixed $value, array $columns): ?Model` | `Model\|null` | First match |
| `create` | `create(array $data): Model` | `Model` | Respects `$fillable`/`$guarded` |
| `update` | `update(int\|string $id, array $data): Model` | `Model` | Finds, updates, returns model |
| `delete` | `delete(int\|string $id): bool` | `bool` | Throws `ModelNotFoundException` if absent |
| `query` | `query(): Builder` | `Builder` | Fresh query builder for complex queries |

### BaseRepository

`MuhammedSalama\Base\Repositories\BaseRepository`

Implements `RepositoryInterface`. Extend it and inject the Eloquent model via the constructor:

```php
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}
```

Additional helper not in the interface:

| Method | Signature | Returns |
|---|---|---|
| `getModel` | `getModel(): Model` | The raw model instance |

**Important:** The model's `$fillable` or `$guarded` controls which attributes can be mass-assigned via `create()` and `update()`. Always define one in your Eloquent model.

### ServiceInterface

`MuhammedSalama\Base\Interfaces\ServiceInterface`

| Method | Signature | Returns | Notes |
|---|---|---|---|
| `all` | `all(array $columns, array $relations): Collection` | `Collection<int, Model>` | |
| `paginate` | `paginate(int $perPage, array $columns, array $relations): LengthAwarePaginator` | `LengthAwarePaginator` | |
| `find` | `find(int\|string $id, array $columns, array $relations): Model` | `Model` | Always throws on missing (calls `findOrFail`) |
| `store` | `store(array $data): Model` | `Model` | |
| `update` | `update(int\|string $id, array $data): Model` | `Model` | |
| `destroy` | `destroy(int\|string $id): bool` | `bool` | |

### BaseService

`MuhammedSalama\Base\Services\BaseService`

Implements `ServiceInterface`. Extend it and inject the repository interface:

```php
class ProductService extends BaseService
{
    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
```

Additional helper not in the interface:

| Method | Returns | Notes |
|---|---|---|
| `repository(): RepositoryInterface` | `RepositoryInterface` | Access the underlying repository for custom queries |

### BaseRequest

`MuhammedSalama\Base\Requests\BaseRequest`

Extends `Illuminate\Foundation\Http\FormRequest`. Override `rules()` in your child class.

| Member | Type | Default | Notes |
|---|---|---|---|
| `authorize()` | `bool` | `true` | Override to add gate/policy checks |
| `rules()` | `array` (abstract) | — | Define validation rules |
| `failedValidation(Validator $v)` | `void` | — | Throws `HttpResponseException` with the `422` envelope |

```php
class StoreProductRequest extends BaseRequest
{
    public function rules(): array
    {
        return ['name' => 'required|string|max:255'];
    }
}
```

### ApiResponse

`MuhammedSalama\Base\Helpers\ApiResponse`

Static helper. All methods return `Illuminate\Http\JsonResponse`.

```php
use MuhammedSalama\Base\Helpers\ApiResponse;

// Success (200)
ApiResponse::success($data, 'Custom message');

// Created (201)
ApiResponse::created($data);

// No Content (204)
ApiResponse::noContent();

// Error (default 400)
ApiResponse::error('Something went wrong', 400, $errorDetails);

// Validation (422)
ApiResponse::validation($validator->errors());

// Not Found (404)
ApiResponse::notFound('Product not found');

// Unauthorized (401)
ApiResponse::unauthorized();

// Forbidden (403)
ApiResponse::forbidden();

// Paginated (200, includes meta block)
ApiResponse::paginated($paginator, 'Success');
```

**Standard success envelope:**

```json
{
    "status": true,
    "message": "Success",
    "data": { ... }
}
```

**Standard error envelope:**

```json
{
    "status": false,
    "message": "Validation error",
    "errors": {
        "name": ["The name field is required."]
    }
}
```

**Paginated envelope:**

```json
{
    "status": true,
    "message": "Success",
    "data": [ ... ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 72
    }
}
```

### ApiResponseTrait

`MuhammedSalama\Base\Traits\ApiResponseTrait`

Use inside controllers to call response methods as `$this->success(...)` instead of the static `ApiResponse::success(...)`. Both produce the identical JSON envelope.

```php
use MuhammedSalama\Base\Traits\ApiResponseTrait;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        return $this->success($data);
    }
}
```

| Method | Parameters | HTTP status |
|---|---|---|
| `success` | `($data, $message, $code)` | 200 (default) |
| `created` | `($data, $message)` | 201 |
| `error` | `($message, $code, $errors)` | 400 (default) |
| `validationError` | `($errors, $message)` | 422 |
| `notFound` | `($message)` | 404 |
| `unauthorized` | `($message)` | 401 |
| `forbidden` | `($message)` | 403 |
| `paginated` | `($paginator, $message)` | 200 |

### ImageUploadTrait

`MuhammedSalama\Base\Traits\ImageUploadTrait`

Use inside controllers or services. Files are stored under `public_path($path)` and the relative path is returned. Extensions are determined from the file's actual MIME type (not the client-supplied filename), protecting against extension-spoofing attacks.

| Method | Returns | Description |
|---|---|---|
| `uploadImage($request, $input, $path)` | `string\|null` | Store a single image |
| `uploadMultiImage($request, $input, $path)` | `array<int, string>` | Store multiple images |
| `updateImage($request, $input, $path, $oldPath)` | `string\|null` | Replace an image, deletes the old file |
| `deleteImage($path)` | `void` | Delete an image |

```php
// Store
$path = $this->uploadImage($request, 'image', 'uploads/products');

// Replace (old file is deleted automatically)
$path = $this->updateImage($request, 'image', 'uploads/products', $product->image);

// Delete
$this->deleteImage($product->image);
```

Always validate uploads in your Form Request before calling these methods:

```php
'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
```

---

## Commands

### `make:repository`

Generates the full Repository–Service–Controller structure for a named resource.

```bash
php artisan make:repository {name} [options]
```

| Argument / Option | Description |
|---|---|
| `name` | Base name, e.g. `Product` (StudlyCase) |
| `--model=` | Eloquent model name when it differs from `name` |
| `--controller=` | Custom controller class name (defaults to `{Name}Controller`) |
| `--no-service` | Skip generating the Service class |
| `--no-controller` | Skip generating the Controller class |
| `--no-request` | Skip Form Request classes (controller falls back to plain `Request`) |
| `--no-migration` | Skip generating the migration |
| `--force` | Overwrite files that already exist |

**Examples:**

```bash
# Full scaffold
php artisan make:repository Product

# Model with a different name
php artisan make:repository BlogPost --model=Post

# Custom controller, skip migration
php artisan make:repository Order --controller=OrderApiController --no-migration

# Minimal: interface + repository only
php artisan make:repository Tag --no-service --no-controller --no-request --no-migration
```

**Generated files:**

```
app/Interfaces/{Name}RepositoryInterface.php
app/Repositories/{Name}Repository.php
app/Services/{Name}Service.php              (unless --no-service)
app/Http/Requests/{Name}/Store{Name}Request.php  (unless --no-request)
app/Http/Requests/{Name}/Update{Name}Request.php (unless --no-request)
app/Http/Controllers/{Controller}.php       (unless --no-controller)
database/migrations/xxxx_create_{table}_table.php (unless --no-migration)
```

When `auto_bind` is enabled (the default), the interface is automatically wired to the repository — no manual binding is needed. See [Configuration](#configuration).

### `base:create-database`

Creates the configured database if it does not already exist. Supports MySQL and PostgreSQL.

```bash
php artisan base:create-database [--connection=]
```

| Option | Description |
|---|---|
| `--connection=` | Laravel database connection name (defaults to `database.default`) |

```bash
# Use the default connection
php artisan base:create-database

# Use a specific connection
php artisan base:create-database --connection=pgsql
```

- If the database already exists the command does nothing and exits successfully.
- MySQL: uses the `charset` and `collation` from the connection config.
- PostgreSQL: uses the `charset` as the `ENCODING`.
- Only `mysql` and `pgsql` drivers are supported.
- Requires the matching PDO extension (`pdo_mysql` / `pdo_pgsql`) and a database user with `CREATE DATABASE` privileges.

---

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=base-config
```

This creates `config/base.php`:

```php
return [

    // When true, App\Interfaces\{Name}RepositoryInterface is automatically
    // bound to App\Repositories\{Name}Repository by naming convention.
    'auto_bind' => true,

    // Manual bindings for anything that doesn't follow the convention.
    'bindings' => [
        // \App\Interfaces\ProductRepositoryInterface::class
        //     => \App\Repositories\EloquentProductRepository::class,
    ],

];
```

**Auto-binding** (enabled by default) scans `app/Interfaces/` for files matching `*RepositoryInterface.php` and binds each to the matching `*Repository` in `app/Repositories/`. Disable it and use `bindings` for full control:

```php
'auto_bind' => false,
'bindings' => [
    \App\Interfaces\ProductRepositoryInterface::class
        => \App\Repositories\ProductRepository::class,
],
```

You can also register bindings in your own `AppServiceProvider` if you prefer:

```php
$this->app->bind(
    \App\Interfaces\ProductRepositoryInterface::class,
    \App\Repositories\ProductRepository::class,
);
```

---

## Publishing helpers and traits

By default, `ApiResponse` and the traits are used directly from the package namespace (`MuhammedSalama\Base\...`) — no copying is needed and you receive fixes automatically when updating the package.

If you want **editable local copies** under the `App\` namespace, publish them:

```bash
# ApiResponse helper → app/Helpers/ApiResponse.php
php artisan vendor:publish --tag=base-helpers

# ApiResponseTrait + ImageUploadTrait → app/Traits/
php artisan vendor:publish --tag=base-traits

# Config → config/base.php
php artisan vendor:publish --tag=base-config
```

After publishing, switch your `use` statements to the `App\Helpers` / `App\Traits` namespaces. Published files will not receive automatic updates — treat them as your own code.

---

## Testing and static analysis

Install dev dependencies:

```bash
composer install
```

Run the test suite (PHPUnit + Orchestra Testbench, SQLite in-memory):

```bash
composer test
```

Run static analysis (PHPStan level 5 + Larastan):

```bash
composer analyse
```

Both commands run on every push and pull request via GitHub Actions across PHP 8.1–8.4 and Laravel 10/11/12/13.

---

## Troubleshooting

**`Deprecation Notice: Function curl_close() is deprecated` during Composer commands.**
These come from Composer running on PHP 8.5+, not from this package. Run `composer self-update` or use PHP 8.4 to suppress them.

**`Your requirements could not be resolved … nette/schema requires php 8.1 - 8.4`.**
A transitive dependency in your project is locked to a version that predates PHP 8.5. Update it: `composer update nette/schema --with-all-dependencies`.

**Auto-binding does not seem to work.**
Check that `app/Interfaces/` exists and the files match the pattern `{Name}RepositoryInterface.php`. Both the interface class and the implementation class must be loadable (verify with `php artisan tinker` → `class_exists()`). Make sure `auto_bind` is `true` in `config/base.php`.

**`Class … not found` after running `make:repository`.**
Run `composer dump-autoload` so the PSR-4 autoloader picks up the newly created files.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a full list of changes by version.

---

## Contributing

Contributions are welcome. Please:

1. Fork the repository and create a feature branch.
2. Write tests for any change in behaviour.
3. Ensure `composer test` and `composer analyse` both pass locally.
4. Open a pull request against `main` with a clear description of the change.
5. **CI must be green before merging.** The workflow runs `composer test` across PHP 8.1–8.4 and Laravel 10/11/12/13 (with appropriate PHP-version excludes), plus PHPStan static analysis. Consider enabling [GitHub branch-protection rules](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches) to require all status checks to pass before a PR can be merged.

---

## Security

If you discover a security vulnerability, please open a confidential issue or contact the author directly. Do **not** disclose vulnerabilities publicly until they have been addressed.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## Author

**Muhammed Salama** — [@Muhammed2024Salama](https://github.com/Muhammed2024Salama)
