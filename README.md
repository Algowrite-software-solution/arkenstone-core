# Arkenstone Core
Laravel based E Commerce Feature targetted Web Application.

##  Requirements

- PHP >= 8.2
- Laravel >= 10.0 (automatically handled via Orchestra Testbench)

##  Naming Conventions
This template uses placeholders that must be replaced throughout the codebase. Here's what each placeholder means:

### Placeholder Reference

| Placeholder | Description | Example |
|------------|-------------|---------|
| `__PackageStudly__` | Package name in StudlyCase (PascalCase) | `PaymentGateway`, `UserManager`, `ApiClient` |
| `__package_name__` | Package name in snake_case | `payment_gateway`, `user_manager`, `api_client` |
| `__PACKAGE__` | Package display name (usually same as StudlyCase) | `Payment Gateway`, `User Manager`, `API Client` |
| `__PACKAGE_ENV_PREFIX__` | Environment variable prefix (UPPER_SNAKE_CASE) | `PAYMENT_GATEWAY`, `USER_MANAGER`, `API_CLIENT` |


### Namespace Convention

All supporting packages should use the namespace format:
```php
Arkenstone\{PackageStudly}
```

**Example**: `Arkenstone\PaymentGateway`, `Arkenstone\UserManager`

##  Template Overview

**This is a basic Laravel package template** that provides the essential structure for creating reusable packages. It includes:

 Service Provider setup  
 Facade support  
 Configuration file  
 Basic testing structure  
 CI/CD workflow  


##  Installation

4. **Update the package metadata:**
   - Replace all placeholders (`__PackageStudly__`, `__package_name__`, `__PACKAGE__`, etc.) in:
     - `composer.json`
     - All files in `src/`
     - `config/__package_name__.php`
     - Test files
     - Namespace declarations

5. **Run tests:**
   ```bash
   composer test
   ```

### For Laravel Application Usage

Intall the package using the following command

```bash
composer require arkenstone
```

## ⚙️ Configuration

### Publishing Configuration

Publish the configuration file to your Laravel application:

```bash
php artisan vendor:publish --tag="arkenstone-config"
```

This will create a `config/arkenstone.php` file in your Laravel app's config directory.

### Environment Variables

The package supports the following environment variables :

```env
__PACKAGE_ENV_PREFIX___ENABLED=true
__PACKAGE_ENV_PREFIX___PREFIX=Arkenstone
```

## 📖 Usage

### Using the Facade

```php
use Arkenstone\Core\Facades\FacadeName;

// Get package version
$version = FacadeName::version();
```

### Using Dependency Injection

```php
use Arkenstone\Core;

class YourController
{
    public function __construct(
        private __PackageStudly__ $package
    ) {}

    public function index()
    {
        return $this->package->version();
    }
}
```

### Using the Service Container

```php
$package = app('__package_name__');
$version = $package->version();
```

##  Testing

This package uses PHPUnit with Orchestra Testbench for Laravel package testing.

### Running Tests

```bash
# Run all tests
composer test

# Or directly with PHPUnit
vendor/bin/phpunit
```

### Writing Tests

All tests should extend the `TestCase` class located in `tests/TestCase.php`. Example:

```php
<?php

namespace arkenstone\__PackageStudly\Tests;

use Arkenstone\Core\__PackageStudly__;

class YourTest extends TestCase
{
    public function testVersionReturnsString(): void
    {
        $pkg = new __PackageStudly__();
        $this->assertIsString($pkg->version());
    }
}
```

##  Development

### Package Structure

```
CEYPK-PackageTemplate/
├── .github/
│   └── workflows/
│       └── tests.yml          # GitHub Actions CI workflow
├── config/
│   └── __package_name__.php   # Package configuration file
├── src/
│   ├── __PackageStudly__.php              # Main package class
│   ├── __PackageStudly__ServiceProvider.php  # Laravel service provider
│   └── Facades/
│       └── __PackageStudly__.php          # Facade class
├── tests/
│   ├── TestCase.php           # Base test case
│   └── ExampleTest.php        # Example test
├── composer.json              # Package metadata
├── phpunit.xml                # PHPUnit configuration
└── README.md                  # This file
```

### Key Files Reference

- **`composer.json`**: Package metadata, dependencies, autoloading configuration, and Laravel package discovery
- **`src/__PackageStudly__.php`**: Main package class containing core functionality
- **`src/__PackageStudly__ServiceProvider.php`**: Registers the package with Laravel and publishes configuration
- **`src/Facades/__PackageStudly__.php`**: Provides facade access to the package
- **`config/__package_name__.php`**: Publishable configuration file
- **`tests/TestCase.php`**: Base test case using Orchestra Testbench
- **`.github/workflows/tests.yml`**: CI/CD workflow for automated testing

### Extending the Template

This is a **basic template** - you can add more features as needed. Here are common extensions:

#### 1. Artisan Commands

Create commands in `src/Console/Commands/`:

```php
<?php

namespace Ceygenic\__PackageStudly__\Console\Commands;

use Illuminate\Console\Command;

class __PackageStudly__Command extends Command
{
    protected $signature = '__package_name__:hello {name?}';
    protected $description = 'Example command';

    public function handle()
    {
        $name = $this->argument('name') ?? 'World';
        $this->info("Hello, {$name}!");
    }
}
```

Register in `__PackageStudly__ServiceProvider::boot()`:
```php
if ($this->app->runningInConsole()) {
    $this->commands([
        Commands\__PackageStudly__Command::class,
    ]);
}
```

#### 2. Database Migrations

Create migrations in `database/migrations/` and publish them:

```php
// In ServiceProvider::boot()
$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

// Or publish migrations
$this->publishes([
    __DIR__ . '/../database/migrations' => database_path('migrations'),
], '__package_name__-migrations');
```

#### 3. Routes

Create `routes/web.php` or `routes/api.php`:

```php
// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use Ceygenic\__PackageStudly__\Http\Controllers\__PackageStudly__Controller;

Route::prefix('api/__package_name__')->group(function () {
    Route::get('/', [__PackageStudly__Controller::class, 'index']);
});
```

Load routes in `ServiceProvider::boot()`:
```php
$this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
```

#### 4. Controllers

Create controllers in `src/Http/Controllers/`:

```php
<?php

namespace Ceygenic\__PackageStudly__\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class __PackageStudly__Controller extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Hello from __PACKAGE__']);
    }
}
```

#### 5. Models

Create models in `src/Models/`:

```php
<?php

namespace Ceygenic\__PackageStudly__\Models;

use Illuminate\Database\Eloquent\Model;

class __PackageStudly__Model extends Model
{
    protected $fillable = ['name', 'email'];
}
```

#### 6. Views

Create views in `resources/views/` and publish them:

```php
// In ServiceProvider::boot()
$this->loadViewsFrom(__DIR__ . '/../resources/views', '__package_name__');

// Or publish views
$this->publishes([
    __DIR__ . '/../resources/views' => resource_path('views/vendor/__package_name__'),
], '__package_name__-views');
```

#### 7. Middleware

Create middleware in `src/Http/Middleware/`:

```php
<?php

namespace Ceygenic\__PackageStudly__\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class __PackageStudly__Middleware
{
    public function handle(Request $request, Closure $next)
    {
        // Your middleware logic
        return $next($request);
    }
}
```

Register in `ServiceProvider::boot()`:
```php
Route::middlewareGroup('__package_name__', [
    Http\Middleware\__PackageStudly__Middleware::class,
]);
```

#### 8. Event Listeners & Observers

Add to `ServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Event;

Event::listen(
    SomeEvent::class,
    SomeListener::class
);

// Or use observers
SomeModel::observe(SomeObserver::class);
```

#### 9. Validation Rules

Create custom rules in `src/Rules/`:

```php
<?php

namespace Ceygenic\__PackageStudly__\Rules;

use Illuminate\Contracts\Validation\Rule;

class __PackageStudly__Rule implements Rule
{
    public function passes($attribute, $value)
    {
        // Validation logic
        return true;
    }

    public function message()
    {
        return 'Validation failed.';
    }
}
```

#### 10. Additional Service Providers

Create additional providers in `src/` and register in `composer.json`:

```json
"extra": {
    "laravel": {
        "providers": [
            "Ceygenic\\__PackageStudly__\\__PackageStudly__ServiceProvider",
            "Ceygenic\\__PackageStudly__\\AdditionalServiceProvider"
        ]
    }
}
```

### Example Extended Structure

```
src/
├── Console/
│   └── Commands/
│       └── __PackageStudly__Command.php
├── Http/
│   ├── Controllers/
│   │   └── __PackageStudly__Controller.php
│   └── Middleware/
│       └── __PackageStudly__Middleware.php
├── Models/
│   └── __PackageStudly__Model.php
├── Rules/
│   └── __PackageStudly__Rule.php
├── Events/
│   └── __PackageStudly__Event.php
├── Listeners/
│   └── __PackageStudly__Listener.php
└── ...

database/
└── migrations/
    └── YYYY_MM_DD_create_table.php

resources/
├── views/
│   └── index.blade.php
└── assets/
    ├── css/
    └── js/

routes/
├── web.php
└── api.php
```

### Laravel Package Creation Guide

1. **Create repository from template**
   - On the GitHub repository page, click the green **"Use this template"** button (located at the top - right  of the repository)
   - Select **"Create a new repository"**
   - Name the new repository using the format: `CEYCDS-PK-COMPOSER-xxx-<repo-name>`
     (Replace `xxx` with the package number and `<repo-name>` with your package name)

2. **Replace placeholders**
   - Update `composer.json` with your package name, description, and author details
   - Replace all `__PackageStudly__` placeholders with your package's StudlyCase name
   - Replace all `__package_name__` placeholders with your package's snake_case name
   - Replace all `__PACKAGE__` placeholders with your package's display name
   - Replace all `__PACKAGE_ENV_PREFIX__` placeholders with your package's environment variable prefix

3. **Implement features**
   - Add your package functionality in `src/__PackageStudly__.php`
   - Extend the service provider if needed for additional bindings
   - Update configuration file as needed

4. **Write tests**
   - Add test cases in `tests/` directory
   - Ensure all tests pass: `composer test`

5. **Documentation**
   - Update this README with your package's specific documentation
   - Document configuration options
   - Provide usage examples

6. **Publish to Packagist**
   - Tag a release: `git tag v1.0.0 && git push --tags`
   - Submit your package to [Packagist](https://packagist.org)

## Continuous Integration

This template includes a GitHub Actions workflow (`.github/workflows/tests.yml`) that automatically runs tests on:
- Push to `main` or `master` branches
- Pull requests targeting `main` or `master` branches

The CI runs on PHP 8.3 with PHPUnit.

##  Additional Resources

For more detailed information about creating Composer packages, refer to the official guide:

- **[Composer Package Creation Guide](https://docs.google.com/document/d/1N6kuRz4Zd2dnZ_QNQcFl48-XJrPV52XC44iQT2tZVB8/edit?usp=sharing)** - Comprehensive guide for package creation

##  Important Notes

- **This is a basic template** - Start with the core structure and add features as needed (see [Extending the Template](#extending-the-template) section)
- **Replace all placeholders** - Before using this template, replace all placeholder values with your actual package names (see [Naming Conventions](#-naming-conventions) section)
- **Follow naming conventions** - Use the repository naming format `CEYCDS-PK-COMPOSER-xxx-<repo-name>` when creating new packages
