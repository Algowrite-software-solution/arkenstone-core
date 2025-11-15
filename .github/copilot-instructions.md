# Arkenstone Core - AI Coding Agent Instructions

## Project Overview

Arkenstone Core is a **Laravel package** (not a standalone application) that provides e-commerce functionality for Laravel applications. It's designed to be installed via Composer and integrates into Laravel projects through service providers.

**Key Characteristics:**

- PHP 8.2+ Laravel package (library type)
- Uses Orchestra Testbench for isolated testing without a full Laravel app
- Namespace: `Arkenstone\Core`
- Auto-discovered via Laravel's package discovery (`extra.laravel` in composer.json)

## Architecture Pattern: Modular Service-Based Design

### Service Provider Hierarchy

The package follows a **nested service provider pattern** for modularity:

```
CoreServiceProvider (main entry point)
  └── Registers ProductServiceProvider
  └── Registers UtilityService singleton
  └── Publishes config files
  └── Bootstraps Event system
```

**When adding new modules:**

1. Create a dedicated `Provider/[Module]ServiceProvider.php` in your module directory
2. Register the provider in `CoreServiceProvider::register()`
3. Bind services as singletons with simple string keys (e.g., `'product'`, `'utility'`)

Example from `ProductServiceProvider`:

```php
$this->app->singleton('product', function () {
    return new ProductService();
});
```

### Contract-First Design

All services MUST implement `Arkenstone\Core\ECommerce\Contracts\Service`:

```php
interface Service {
    public function getName(): string;
}
```

Check `src/ECommerce/Contracts/` for available interfaces before creating services.

## Event System: WordPress-Inspired Hooks

Arkenstone uses a **custom event wrapper** (`Arkenstone\Core\Support\Event`) that mimics WordPress hooks:

```php
// Listen to events (like add_filter)
Event::hook('response.success', function($data, $message, $code) {
    // Modify and return $data
    return $modifiedData;
});

// Dispatch events (like apply_filters)
Event::dispatch('response.success', [$data, $message, $code]);
```

**Built-in events:**

- `response.success` - Fired on `ResponseProtocol::success()`
- `response.error` - Fired on `ResponseProtocol::error()`

The Event class is initialized in `CoreServiceProvider::boot()` by attaching Laravel's event dispatcher.

## Response Standard: ResponseProtocol

**Always use `ResponseProtocol` for API responses**, not `response()->json()` directly:

```php
use Arkenstone\Core\Helpers\ResponseProtocol;

// Success response
return ResponseProtocol::success($data, 'Operation successful', 200);
// Output: {"status": "success", "message": "...", "data": {...}}

// Error response
return ResponseProtocol::error($errors, 'Validation failed', 422);
// Output: {"status": "error", "message": "...", "errors": {...}}
```

This fires hook events and maintains consistent response structure across the package.

## Eloquent Model Patterns

### Query Scopes for Filtering

Models use **scope methods** extensively for reusable query filters:

```php
// From Product model
Product::query()
    ->isActive()
    ->filterByName('laptop')
    ->minPrice(100)
    ->byCategories([1, 2, 3])
    ->get();
```

**Scope naming convention:**

- `scopeIsActive` → `isActive()`
- `scopeFilterByName` → `filterByName($name)`
- `scopeByCategories` → `byCategories($ids)` (OR logic)
- `scopeByAllCategories` → `byAllCategories($ids)` (AND logic)

### Relationships Structure

Products use a **flexible taxonomy system**:

- `categories()` - BelongsToMany via `product_categories` pivot
- `brand()` - BelongsTo relationship
- `taxonomies()` - Generic BelongsToMany for custom attributes
- `images()` - HasMany with `primaryImage()` convenience method

Check `src/ECommerce/Product/Models/Product.php` for the complete relationship map.

## Testing Setup

### Base TestCase Configuration

All tests extend `Arkenstone\Core\Tests\TestCase` which:

1. Uses Orchestra Testbench for isolated Laravel environment
2. Auto-loads `CoreServiceProvider` (and nested providers)
3. Registers facades in `getPackageAliases()`

```php
class MyFeatureTest extends TestCase {
    public function test_my_feature() {
        $service = app()->make('product'); // Access via container
        $this->assertIsArray($service->getProducts([]));
    }
}
```

### Running Tests

```bash
composer test          # Runs vendor/bin/phpunit
vendor/bin/phpunit     # Direct PHPUnit execution
```

Test files must end with `Test.php` (e.g., `ProductTest.php`).

## Directory Structure Logic

```
src/
  ECommerce/           # Domain-specific modules
    Contracts/         # Shared interfaces for services
    Product/           # Product module (self-contained)
      Http/
      Models/
      Services/
      Provider/        # Module service provider
    Order/             # Future module (follow Product structure)
    Stock/             # Future module
  Helpers/             # Static utility classes (ResponseProtocol)
  Services/            # General-purpose services (UtilityService)
  Support/             # Core infrastructure (Event system)
  Facades/             # Laravel facades for services
```

**When adding features:**

- E-commerce domain logic → `src/ECommerce/[Module]/`
- Cross-cutting utilities → `src/Helpers/` or `src/Services/`
- Framework integration → `src/Support/`

## Configuration

Package config is merged and publishable:

```php
// Access in code
config('arkenstone.enabled')
config('arkenstone.default_prefix')

// Publish to host app
php artisan vendor:publish --tag=arkenstone-config
```

Environment variables: `ARKENSTONE_CORE_ENABLED`, `ARKENSTONE_CORE_PREFIX`

## Common Pitfalls

1. **Don't instantiate services directly** - Use `app()->make('service-key')` or dependency injection
2. **Tests must extend the package TestCase** - Not Laravel's base TestCase
3. **Service providers should call parent::boot()** - Especially `RouteServiceProvider` subclasses
4. **Event system requires dispatcher initialization** - Already done in `CoreServiceProvider::boot()`

## Key Files to Reference

- `src/CoreServiceProvider.php` - Package registration entry point
- `src/Support/Event.php` - Custom event system implementation
- `src/ECommerce/Product/Provider/ProductServiceProvider.php` - Module provider example
- `tests/TestCase.php` - Base test configuration
- `src/ECommerce/Product/Models/Product.php` - Model patterns and scopes
