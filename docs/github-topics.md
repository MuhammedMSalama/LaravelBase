# Recommended GitHub Repository Topics

Add these topics on the repository's GitHub page under **About → Topics**.
Topics drive organic discovery through GitHub search and the Explore page.

---

## How to apply

1. Go to `https://github.com/Muhammed2024Salama/LaravelBase`.
2. Click the gear icon next to **About**.
3. Paste each topic from the list below into the **Topics** field.
4. Save.

---

## Topics and rationale

| Topic | Why |
|---|---|
| `laravel` | Primary ecosystem tag — required for GitHub and Packagist discoverability |
| `php` | Catches PHP-language searches outside the Laravel ecosystem |
| `artisan` | Specifically targets developers searching for Artisan generators |
| `code-generator` | The primary job this package performs |
| `repository-pattern` | The architectural pattern the package implements |
| `service-layer` | Complementary pattern — developers searching for service-layer scaffolding will find this |
| `rest-api` | Positions the package in the API development category |
| `swagger` | Targets developers who care about API documentation |
| `openapi` | Canonical term for the OpenAPI specification |
| `module-generator` | Already in `composer.json` keywords — mirror it as a topic |

---

## Exact string to paste (space-separated)

```
laravel php artisan code-generator repository-pattern service-layer rest-api swagger openapi module-generator
```

---

## Expected effect

- `laravel code-generator` searches on GitHub will surface this repository.
- The Packagist page pulls keywords from `composer.json` (already configured);
  GitHub topics improve discoverability for developers who never reach Packagist.
- Laravel News editors use GitHub topics when curating packages — `laravel` is
  required for their discovery filters.
