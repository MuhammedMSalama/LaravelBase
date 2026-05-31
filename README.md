<h1 align="center">Laravel Base</h1>

<p align="center">
  A lightweight, framework-version-agnostic Laravel package that ships a clean, opinionated architecture out of the box —
  <strong>Interfaces, Repositories, Services, external Form-Request validation, Helpers (ApiResponse), and Traits</strong>.
</p>

<p align="center">
  <a href="https://packagist.org/packages/muhammedsalama/laravel-base"><img src="https://img.shields.io/packagist/v/muhammedsalama/laravel-base?style=flat-square" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/muhammedsalama/laravel-base"><img src="https://img.shields.io/packagist/dt/muhammedsalama/laravel-base?style=flat-square" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/muhammedsalama/laravel-base"><img src="https://img.shields.io/packagist/php-v/muhammedsalama/laravel-base?style=flat-square" alt="PHP Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-green?style=flat-square" alt="License: MIT"></a>
</p>

---

## Table of Contents

- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Package Structure](#package-structure)
- [Usage](#usage)
  - [1. Create an Interface](#1-create-an-interface)
  - [2. Create a Repository](#2-create-a-repository)
  - [3. Create a Service](#3-create-a-service)
  - [4. Create Form Requests (External Validation)](#4-create-form-requests-external-validation)
  - [5. Register the Binding](#5-register-the-binding)
  - [6. Use It in a Controller](#6-use-it-in-a-controller)
- [API Reference](#api-reference)
  - [RepositoryInterface](#repositoryinterface)
  - [ServiceInterface](#serviceinterface)
  - [BaseRequest](#baserequest)
  - [ApiResponse Helper](#apiresponse-helper)
  - [ImageUploadTrait](#imageuploadtrait)
- [Configuration](#configuration)
- [Publishing to Packagist](#publishing-to-packagist)
- [License](#license)
- [Author](#author)

---

## About

**Laravel Base** removes the repetitive boilerplate of setting up the Repository–Service pattern in every new project. Instead of rewriting the same base classes, contracts, and helpers over and over, you extend a small set of well-tested base components and focus on your business logic.

It keeps validation **outside** the controller through dedicated Form Request classes, and bundles two everyday utilities: a consistent **JSON API response** layer and a battle-tested **image upload trait**.

The package is intentionally **not tied to a specific Laravel release** — it works with Laravel 10 and every version after it.

---

## Features

- **Repository pattern** — a fully implemented `BaseRepository` with CRUD, eager loading, pagination, and query access.
- **Service layer** — a `BaseService` that wraps any repository and keeps controllers thin.
- **Contracts first** — `RepositoryInterface` and `ServiceInterface` to keep your code decoupled and testable.
- **External validation** — a `BaseRequest` (Form Request) that moves validation out of controllers and returns failures in the standard API envelope automatically.
- **Consistent API responses** — a static `ApiResponse` helper plus an `ApiResponseTrait` for controllers, both producing an identical JSON envelope.
- **Image handling** — upload, multi-upload, update (auto-deletes the old file), and delete via `ImageUploadTrait`.
- **Framework-agnostic** — no upper bound on the Laravel version.
- **Auto-discovery** — the service provider registers automatically; bindings are configurable.

---

## Requirements

| Dependency | Version              |
| ---------- | -------------------- |
| PHP        | `^8.1`               |
| Laravel    | `10.0` and above     |

---

## Installation

### Via Packagist (recommended)

```bash
composer require muhammedsalama/laravel-base
```

That's it — the service provider is registered automatically through Laravel's package auto-discovery.

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=base-config
```

<details>
<summary>Alternative: install directly from GitHub (VCS)</summary>

Add the repository to your project's `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/Muhammed2024Salama/laravel-base"
    }
]
```

```bash
composer require muhammedsalama/laravel-base:^1.0
```

</details>

<details>
<summary>Alternative: install from a local path (for development)</summary>

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

## Package Structure

```
src/
├── BaseServiceProvider.php
├── Console/
│   ├── Commands/
│   │   └── MakeRepositoryCommand.php
│   └── Stubs/
│       ├── interface.stub
│       ├── repository.stub
│       ├── service.stub
│       └── controller.stub
├── Interfaces/
│   ├── RepositoryInterface.php
│   └── ServiceInterface.php
├── Repositories/
│   └── BaseRepository.php
├── Services/
│   └── BaseService.php
├── Requests/
│   └── BaseRequest.php
├── Helpers/
│   └── ApiResponse.php
└── Traits/
    ├── ApiResponseTrait.php
    └── ImageUploadTrait.php
stubs/app/            # editable copies for vendor:publish
├── Helpers/
│   └── ApiResponse.php
└── Traits/
    ├── ApiResponseTrait.php
    └── ImageUploadTrait.php
config/
└── base.php
```

---

## Usage

### Quick start with the generator

Scaffold the whole structure for a resource with a single command:

```bash
php artisan make:repository Product
```

This generates:

```
app/Interfaces/ProductRepositoryInterface.php
app/Repositories/ProductRepository.php
app/Services/ProductService.php
app/Http/Requests/Product/StoreProductRequest.php
app/Http/Requests/Product/UpdateProductRequest.php
app/Http/Controllers/ProductController.php
database/migrations/xxxx_create_products_table.php
```

The generated controller is fully wired: it injects the service, uses `ApiResponseTrait`, type-hints the generated Form Requests, and ships with `index`, `show`, `store`, `update`, and `destroy`. Validation lives in the request classes (extending `BaseRequest`), so failures return the standard `422` envelope automatically.

Options:

| Option                        | Description                                                  |
| ----------------------------- | ------------------------------------------------------------ |
| `--model=Post`                | Wrap a model whose name differs from the argument.           |
| `--controller=BlogController` | Use a custom controller name (defaults to `{Name}Controller`). |
| `--no-service`                | Skip generating the Service class.                           |
| `--no-controller`             | Skip generating the Controller class.                        |
| `--no-request`                | Skip the Form Request classes (controller falls back to a plain `Request`). |
| `--no-migration`              | Skip generating the migration.                               |
| `--force`                     | Overwrite existing files.                                    |

Examples:

```bash
php artisan make:repository Post                          # full set
php artisan make:repository Post --controller=BlogController
php artisan make:repository Post --no-request --no-migration
```

Thanks to **auto-binding**, the generated interface is wired to its repository automatically — you can inject `ProductService` (or `ProductRepositoryInterface`) right away, no manual binding required.

> Auto-binding follows the convention `App\Interfaces\{Name}RepositoryInterface` → `App\Repositories\{Name}Repository`. Disable it by setting `auto_bind => false` in `config/base.php`, then register bindings manually (see below).

### Manual setup

If you prefer to create the files yourself, the following walks through wiring a complete `Product` resource from contract to controller.

### 1. Create an Interface

```php
namespace App\Interfaces;

use MuhammedSalama\Base\Interfaces\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    // Add Product-specific query methods here if needed.
}
```

### 2. Create a Repository

```php
namespace App\Repositories;

use App\Models\Product;
use App\Interfaces\ProductRepositoryInterface;
use MuhammedSalama\Base\Repositories\BaseRepository;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }
}
```

### 3. Create a Service

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

### 4. Create Form Requests (External Validation)

Validation lives in its own classes, not in the controller. Extend the package's `BaseRequest`, which automatically returns a consistent JSON envelope (`422`) on failure.

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

A failed validation response looks like:

```json
{
    "status": false,
    "message": "Validation error",
    "errors": {
        "name": ["The name field is required."]
    }
}
```

### 5. Register the Binding

Map the interface to its implementation in `config/base.php`:

```php
'bindings' => [
    \App\Interfaces\ProductRepositoryInterface::class => \App\Repositories\ProductRepository::class,
],
```

> You may also bind it in your own `AppServiceProvider` if you prefer.

### 6. Use It in a Controller

The controller stays thin: validation is type-hinted, business logic lives in the service, responses are consistent.

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

    public function show($id)
    {
        return $this->success($this->service->find($id));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['image'] = $this->uploadImage($request, 'image', 'uploads/products');

        return $this->created($this->service->store($data));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        return $this->success($this->service->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        $this->service->destroy($id);

        return $this->success(null, 'Deleted successfully');
    }
}
```

---

## API Reference

### RepositoryInterface

| Method                                     | Description                                      |
| ------------------------------------------ | ------------------------------------------------ |
| `all($columns, $relations)`                | Get all records.                                 |
| `paginate($perPage, $columns, $relations)` | Get paginated records.                           |
| `find($id, $columns, $relations)`          | Find by primary key (returns `null` if absent).  |
| `findOrFail($id, $columns, $relations)`    | Find by primary key or throw.                    |
| `findBy($column, $value, $columns)`        | Find the first record matching a column.         |
| `create($data)`                            | Create a new record.                             |
| `update($id, $data)`                       | Update an existing record.                       |
| `delete($id)`                              | Delete a record.                                 |
| `query()`                                  | Get a fresh query builder for custom queries.    |

### ServiceInterface

| Method                                     | Description                 |
| ------------------------------------------ | --------------------------- |
| `all($columns, $relations)`                | Delegate to the repository. |
| `paginate($perPage, $columns, $relations)` | Delegate to the repository. |
| `find($id, $columns, $relations)`          | Find or fail.               |
| `store($data)`                             | Create a record.            |
| `update($id, $data)`                       | Update a record.            |
| `destroy($id)`                             | Delete a record.            |

### BaseRequest

Extend this abstract Form Request to keep validation outside controllers.

| Member                          | Description                                                          |
| ------------------------------- | -------------------------------------------------------------------- |
| `authorize()`                   | Returns `true` by default. Override for gate/policy checks.          |
| `rules()` *(abstract)*          | Define your validation rules in the child request.                   |
| `failedValidation($validator)`  | Throws an `HttpResponseException` with the standard `422` envelope.  |

### ApiResponse Helper

Use the static helper directly, or the `ApiResponseTrait` inside controllers — both return the same envelope.

```php
use MuhammedSalama\Base\Helpers\ApiResponse;

ApiResponse::success($data, 'Success', 200);
ApiResponse::created($data);
ApiResponse::error('Something went wrong', 400, $errors);
ApiResponse::validation($errors);
ApiResponse::notFound();
ApiResponse::unauthorized();
ApiResponse::forbidden();
ApiResponse::paginated($paginator);
ApiResponse::noContent();
```

**Standard JSON envelope:**

```json
{
    "status": true,
    "message": "Success",
    "data": {}
}
```

### ImageUploadTrait

| Method                                           | Returns        | Description                                      |
| ------------------------------------------------ | -------------- | ------------------------------------------------ |
| `uploadImage($request, $input, $path)`           | `string\|void` | Store a single image, returns its relative path. |
| `uploadMultiImage($request, $input, $path)`      | `array\|void`  | Store multiple images, returns their paths.      |
| `updateImage($request, $input, $path, $oldPath)` | `string\|void` | Replace an image and delete the old one.         |
| `deleteImage($path)`                             | `void`         | Delete an image from disk.                       |

```php
$path = $this->uploadImage($request, 'image', 'uploads/products');
$path = $this->updateImage($request, 'image', 'uploads/products', $product->image);
$this->deleteImage($product->image);
```

---

## Creating the Database

The package can create your database if it doesn't exist yet, using whichever driver is set in your `.env` (MySQL or PostgreSQL):

```bash
php artisan base:create-database
```

It reads the default connection from `config/database.php`, and:

- if the database already exists, it does nothing;
- if it doesn't, it creates it using the proper syntax for the driver — MySQL with the configured charset/collation, PostgreSQL with the configured encoding.

Use a non-default connection with `--connection`:

```bash
php artisan base:create-database --connection=pgsql
```

> Requires the matching PDO driver (`pdo_mysql` / `pdo_pgsql`) and a DB user with `CREATE DATABASE` privileges. Only `mysql` and `pgsql` are supported.

---

## Publishing the Helper & Traits

By default the `ApiResponse` helper and the traits are used directly from the package namespace (`use MuhammedSalama\Base\Helpers\ApiResponse;`) — no copying needed, and you get updates for free.

If you want **editable local copies** inside your app (with the correct `App\` namespace), publish them:

```bash
php artisan vendor:publish --tag=base-helpers   # -> app/Helpers/ApiResponse.php
php artisan vendor:publish --tag=base-traits    # -> app/Traits/ApiResponseTrait.php, app/Traits/ImageUploadTrait.php
php artisan vendor:publish --tag=base-config     # -> config/base.php
```

The published files use the `App\Helpers` / `App\Traits` namespaces, so you can edit them freely. Switch your imports to `App\Helpers\ApiResponse` / `App\Traits\...` if you go this route.

---

## Configuration

After publishing, edit `config/base.php` to register your interface-to-implementation bindings:

```php
return [
    'bindings' => [
        \App\Interfaces\ProductRepositoryInterface::class => \App\Repositories\ProductRepository::class,
    ],
];
```

The service provider loops over this array and binds each pair into the container automatically.

## Troubleshooting

**`Deprecation Notice: Function curl_close() is deprecated ...` during `composer` commands.**
These notices come from Composer itself running on PHP 8.5 — not from this package. They appear on every Composer command and are harmless. Update Composer with `composer self-update`, or use PHP 8.4, to silence them.

**`Your requirements could not be resolved ... nette/schema requires php 8.1 - 8.4`.**
A transitive dependency in your project was locked to a version that predates PHP 8.5 support. Update it: `composer update nette/schema --with-all-dependencies`, then install/require again.

---

## Development

Clone the repository and install the dev dependencies:

```bash
composer install
```

Run the test suite (PHPUnit + Orchestra Testbench):

```bash
composer test
```

Run static analysis (PHPStan / Larastan):

```bash
composer analyse
```

Both run automatically on every push and pull request via GitHub Actions (see `.github/workflows`).

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## Author

**Muhammed Salama**
GitHub: [@Muhammed2024Salama](https://github.com/Muhammed2024Salama)
