# Arkenstone Core - Testing Documentation

## Overview

This document outlines the testing strategy, test structure, and commands for the Arkenstone Core Laravel package.

## Testing Tools

- **PHPUnit 11.5.44** - Primary testing framework
- **Orchestra Testbench ^10.0** - Isolated Laravel environment for package testing
- **Laravel Testing Utilities** - HTTP testing, database assertions, JSON validation
- **SQLite (in-memory)** - Fast database testing without persistence
- **Faker** - Test data generation via Model Factories

## Running Tests

### Run All Tests
```bash
composer test
# or
vendor/bin/phpunit
# or via Orchestra Testbench
vendor/bin/testbench package:test
```

### Run Specific Test File
```bash
vendor/bin/phpunit tests/Feature/API/V1/ProductControllerTest.php
```

### Run Specific Test Method
```bash
vendor/bin/phpunit --filter test_method_name
# Example:
vendor/bin/phpunit --filter it_can_list_all_products
```

### Run Tests with Detailed Output
```bash
# Orchestra Testbench provides detailed output with execution times
vendor/bin/testbench package:test
```

### Run Tests with Coverage (if configured)
```bash
vendor/bin/phpunit --coverage-html coverage
```

## Test Structure

```
tests/
├── TestCase.php                          # Base test case with package setup
├── Unit/
│   ├── Providers/
│   │   └── ServiceProviderTest.php       # Service registration tests
│   ├── Helpers/
│   │   └── ResponseProtocolTest.php      # Response formatting tests
│   ├── Support/
│   │   └── EventTest.php                 # Event system tests
│   ├── Models/
│   │   └── ProductTest.php               # Product model tests
│   └── Services/
│       ├── BrandServiceTest.php          # Brand service unit tests
│       ├── CategoryServiceTest.php       # Category service unit tests
│       ├── TaxonomyServiceTest.php       # Taxonomy service unit tests
│       └── ProductImageServiceTest.php   # Product image service unit tests
└── Feature/
    ├── RouteLoadingTest.php              # Route registration tests
    └── API/
        └── V1/
            ├── ProductControllerTest.php         # Product API endpoints
            ├── BrandControllerTest.php           # Brand API endpoints
            ├── CategoryControllerTest.php        # Category API endpoints
            ├── TaxonomyControllerTest.php        # Taxonomy API endpoints
            ├── TaxonomyTypeControllerTest.php    # Taxonomy type API endpoints
            ├── ProductImageControllerTest.php    # Product image API endpoints
            └── ProductTaxonomyControllerTest.php # Product-taxonomy relationships
```

## Test Plan

### Phase 1: Foundation Tests (52 tests) ✅

#### Service Provider Tests (10 tests)
- ✅ Core service provider registers correctly
- ✅ All 7 services register as singletons (Product, Brand, Category, Taxonomy, ProductImage, ProductTaxonomy, Utility)
- ✅ Config file publishes correctly
- ✅ Routes load properly
- ✅ Event dispatcher attached on boot

#### Response Protocol Tests (8 tests)
- ✅ Success response structure (`status`, `message`, `data`)
- ✅ Error response structure via `ResponseProtocol::failed()` (`status`, `message`, `errors`)
- ✅ HTTP status codes (200, 201, 400, 404, 422, 500)
- ✅ Event dispatching on success/error
- ✅ Default status codes (200 for success, 400 for failed)
- ✅ Optional message parameter handling

#### Product Model Tests (16 tests)
- ✅ Implements ProductContract interface
- ✅ Auto-generates slug from name on creation
- ✅ Respects manually set slug
- ✅ hasDiscount() returns false when no discount
- ✅ hasDiscount() returns false when discount value is zero
- ✅ hasDiscount() returns true when discount is set
- ✅ salePrice() is null when no discount
- ✅ salePrice() calculates correctly for percentage discount
- ✅ salePrice() calculates correctly for fixed_amount discount
- ✅ salePrice() never goes below zero for fixed_amount
- ✅ 100% percentage discount makes product free
- ✅ salePrice() rounds to two decimal places
- ✅ Casts discount_type to enum
- ✅ Casts discount_value to decimal
- ✅ Casts price to decimal
- ✅ Casts is_active to boolean

#### Event System Tests (7 tests)
- ✅ WordPress-style hook registration (`Event::hook()`)
- ✅ Event dispatching (`Event::dispatch()`)
- ✅ Multiple listeners on same event
- ✅ Data transformation through event chain
- ✅ Laravel event dispatcher integration

#### Route Loading Tests (4 tests)
- ✅ All 35 API routes registered
- ✅ Unique route names
- ✅ Correct middleware assignment (`api`)
- ✅ Correct URL prefix (`/api/v1`)

### Phase 2: Service Unit Tests (38 tests) ✅

#### Brand Service (11 tests)
- ✅ Get all brands
- ✅ Get brand by ID
- ✅ Create brand
- ✅ Update brand
- ✅ Delete brand
- ✅ Query brands with pagination
- ✅ Uses default limit (15) when not provided
- ✅ Returns brands ordered by latest
- ✅ Returns false when updating non-existent brand
- ✅ Returns false when deleting non-existent brand
- ✅ Null handling for non-existent brands

#### Category Service (8 tests)
- ✅ Get all categories
- ✅ Get category by ID
- ✅ Create category
- ✅ Update category
- ✅ Delete category
- ✅ Get category children (hierarchical)
- ✅ Get root categories
- ✅ Root categories have null parent_id

#### Taxonomy Service (13 tests)
- ✅ List taxonomies with pagination
- ✅ Uses default pagination when not provided
- ✅ Filter taxonomies by taxonomy_type_id
- ✅ Filter taxonomies by type_slug
- ✅ Filter taxonomies by parent_id
- ✅ Filter root taxonomies only
- ✅ Search taxonomies by name
- ✅ Loads relationships when listing
- ✅ Combine multiple filters
- ✅ Create taxonomy
- ✅ Update taxonomy
- ✅ Delete taxonomy
- ✅ Get active taxonomies

#### Product Image Service (9 tests)
- ✅ Get images by product ID
- ✅ Get image by ID
- ✅ Create product image
- ✅ Update product image
- ✅ Delete product image
- ✅ Set primary image
- ✅ Get primary image
- ✅ Only one image can be primary per product
- ✅ Returns null when no primary image exists

### Phase 3: API Feature Tests (86 tests) ✅

#### Product API (16 tests)
**Listing & Filtering:**
- ✅ List all products with pagination
- ✅ Filter by name (search)
- ✅ Filter by brand_id
- ✅ Filter by min_price
- ✅ Filter by max_price
- ✅ Filter by category_ids (array)
- ✅ Filter by is_active

**CRUD Operations:**
- ✅ Show single product
- ✅ Create product
- ✅ Update product
- ✅ Delete product (soft delete)
- ✅ 404 for non-existent products
- ✅ 404 when deleting non-existent product

**Validation:**
- ✅ Required fields (name, price, sku, brand_id)
- ✅ Unique SKU validation
- ✅ Attach categories on creation

#### Brand API (10 tests)
- ✅ List all brands
- ✅ Show single brand
- ✅ Create brand
- ✅ Update brand
- ✅ Delete brand (soft delete)
- ✅ Validate required fields (name, slug)
- ✅ Validate unique slug
- ✅ 404 error handling

#### Category API (13 tests)
- ✅ List all categories
- ✅ Show single category
- ✅ Create category
- ✅ Update category
- ✅ Delete category (soft delete)
- ✅ Get category children (hierarchical)
- ✅ Get root categories
- ✅ Validate required fields (name, slug)
- ✅ Validate unique slug
- ✅ Prevent self-parent relationship
- ✅ Handle empty children array
- ✅ 404 error handling

#### Taxonomy API (11 tests)
- ✅ List all taxonomies
- ✅ Show single taxonomy
- ✅ Create taxonomy
- ✅ Update taxonomy
- ✅ Delete taxonomy (soft delete)
- ✅ Get taxonomies by type
- ✅ Create taxonomy with meta data (JSON)
- ✅ Validate required fields (name, taxonomy_type_id)
- ✅ Validate taxonomy_type_id exists
- ✅ 404 error handling
- ✅ Returns empty array for type with no taxonomies

#### Taxonomy Type API (12 tests)
- ✅ List all taxonomy types
- ✅ Show single taxonomy type
- ✅ Create taxonomy type
- ✅ Update taxonomy type
- ✅ Delete taxonomy type (soft delete)
- ✅ Validate name is required
- ✅ Validate name is unique
- ✅ Validate slug is unique
- ✅ 404 when taxonomy type not found
- ✅ Filter taxonomy types by search
- ✅ Paginate taxonomy types
- ✅ Load taxonomies with taxonomy type

#### Product Image API (13 tests)
- ✅ List images for product
- ✅ Returns empty array for product with no images
- ✅ Show single product image
- ✅ Create product image
- ✅ Update product image
- ✅ Delete product image (soft delete)
- ✅ Set primary image
- ✅ Ensure only one image is primary per product
- ✅ Get primary image
- ✅ Validate required fields (product_id, image_url)
- ✅ Validate product exists when creating image
- ✅ 404 for non-existent image
- ✅ 404 when no primary image exists

#### Product Taxonomy API (16 tests)
**Relationship Queries:**
- ✅ Get taxonomies for product
- ✅ Get products for taxonomy

**Attach:**
- ✅ Attach taxonomies to product
- ✅ Report already attached taxonomies
- ✅ Validate required fields
- ✅ Validate array structure
- ✅ Validate minimum 1 taxonomy

**Sync:**
- ✅ Sync taxonomies (replace all)
- ✅ Sync empty array to detach all
- ✅ Validate required fields

**Detach:**
- ✅ Detach taxonomies from product
- ✅ Report not attached taxonomies
- ✅ Validate required fields

## Test Results

```
PHPUnit 11.5.44 by Sebastian Bergmann and contributors.
Runtime: PHP 8.3.10
Configuration: phpunit.xml

Tests: 176 passed (595 assertions)
Duration: ~20 seconds
Memory: 50 MB
Status: ✅ ALL PASSING
```

**Via Orchestra Testbench:**
```bash
vendor/bin/testbench package:test

Tests:    176 passed (595 assertions)
Duration: 20.99s
Status:   ✅ ALL PASSING
```

### Coverage Summary

| Category | Tests | Status |
|----------|-------|--------|
| **Foundation** | 52 | ✅ |
| Service Providers | 10 | ✅ |
| Response Protocol | 8 | ✅ |
| Product Model | 16 | ✅ |
| Event System | 7 | ✅ |
| Route Loading | 5 | ✅ |
| ProductTest (Legacy) | 1 | ✅ |
| **Service Unit** | 38 | ✅ |
| Brand Service | 11 | ✅ |
| Category Service | 8 | ✅ |
| Taxonomy Service | 13 | ✅ |
| Product Image Service | 9 | ✅ |
| **API Feature** | 86 | ✅ |
| Product API | 16 | ✅ |
| Brand API | 10 | ✅ |
| Category API | 12 | ✅ |
| Taxonomy API | 11 | ✅ |
| Taxonomy Type API | 12 | ✅ |
| Product Image API | 13 | ✅ |
| Product Taxonomy API | 14 | ✅ |
| **TOTAL** | **176** | **✅** |

## Test Infrastructure

### Database Setup

Tests use SQLite in-memory database for speed and isolation:

```php
// tests/TestCase.php
protected function getEnvironmentSetUp($app)
{
    $app['config']->set('database.default', 'testing');
    $app['config']->set('database.connections.testing', [
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);
}
```

### Migrations

All migrations automatically load via:

```php
protected function setUp(): void
{
    parent::setUp();
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
}
```

### Model Factories

Located in `database/factories/`:
- BrandFactory
- CategoryFactory
- TaxonomyTypeFactory
- TaxonomyFactory
- ProductFactory
- ProductImageFactory

**Factory States:**
- `Brand`: `active()`, `inactive()`
- `Category`: `withParent()`, `active()`, `inactive()`
- `Taxonomy`: `withParent()`, `withMeta()`, `active()`, `inactive()`
- `Product`: `onSale()`, `outOfStock()`, `active()`, `featured()`, `withoutBrand()`
- `ProductImage`: `primary()`

### RefreshDatabase Trait

All feature tests use `RefreshDatabase` to ensure clean state:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    // Tests run in isolated database
}
```

## API Response Structure

All API responses follow the ResponseProtocol standard:

### Success Response
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": {
    // Resource data or collection with meta/links
  }
}
```

### Collection Response
```json
{
  "status": "success",
  "message": "Resources retrieved",
  "data": {
    "data": [...],
    "meta": {
      "total": 100,
      "count": 15,
      "per_page": 15,
      "current_page": 1,
      "total_pages": 7
    },
    "links": {
      "first": "...",
      "last": "...",
      "prev": null,
      "next": "..."
    }
  }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Operation failed",
  "errors": {
    "field": ["Error message"]
  }
}
```

**Note:** Error responses use `ResponseProtocol::failed($errors, $message, $code)` which dispatches `response.error` event.

## Testing Best Practices

### 1. Test Isolation
- Use `RefreshDatabase` trait
- Don't rely on test execution order
- Create all needed data in each test

### 2. Descriptive Test Names
```php
/** @test */
public function it_can_filter_products_by_price_range()
{
    // Clear intent from name
}
```

### 3. Arrange-Act-Assert Pattern
```php
// Arrange
$product = Product::factory()->create(['price' => 100]);

// Act
$response = $this->getJson('/api/v1/products?min_price=50');

// Assert
$response->assertStatus(200);
$this->assertCount(1, $response->json('data.data'));
```

### 4. Test Both Success and Failure
- Valid input → successful response
- Invalid input → validation errors
- Non-existent resource → 404

### 5. Use Factories
```php
// Good - flexible, readable
Brand::factory()->count(5)->create();

// Avoid - brittle, verbose
Brand::create(['name' => 'Nike', 'slug' => 'nike', ...]);
```

## Future Test Additions

### Phase 4: Additional Service Tests
- [ ] ProductService full implementation tests
- [ ] ProductTaxonomyService unit tests
- [ ] TaxonomyTypeService unit tests
- [ ] UtilityService tests

### Phase 5: Additional Model Tests
- [x] Product model (16 tests completed)
- [ ] Product model scopes (isActive, filterByName, minPrice, maxPrice, etc.)
- [ ] Product relationships (categories, taxonomies, images)
- [ ] Brand model tests
- [ ] Category model tests (hierarchy)
- [ ] Taxonomy model tests (parent-child relationships)
- [ ] TaxonomyType model tests
- [ ] ProductImage model tests

### Phase 6: Request Validation Tests
- [ ] All 13 Form Request classes
- [ ] Custom validation rules
- [ ] Error message customization

### Phase 7: Resource Transformation Tests
- [ ] All 12 Resource/Collection classes
- [ ] Conditional field inclusion
- [ ] Relationship loading

### Phase 8: Integration Tests
- [ ] Complete product creation workflow
- [ ] Product with images and taxonomies
- [ ] Category tree operations
- [ ] Bulk operations

### Phase 9: Performance Tests
- [ ] N+1 query prevention
- [ ] Eager loading verification
- [ ] Large dataset handling

## Continuous Integration

### GitHub Actions (Recommended)

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        extensions: mbstring, sqlite3
        
    - name: Install Dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run Tests
      run: composer test
```

## Troubleshooting

### Tests Fail Due to Missing Tables
```bash
# Ensure migrations are in database/migrations/
# Check TestCase::setUp() loads migrations correctly
```

### Factory Not Found
```bash
# Run: composer dump-autoload
# Check composer.json autoload section includes factories
```

### Memory Limit Issues
```bash
php -d memory_limit=512M vendor/bin/phpunit
```

### Slow Tests
```bash
# Use SQLite in-memory (already configured)
# Reduce factory data generation
# Use fewer assertions per test
```

## Conclusion

The Arkenstone Core package has comprehensive test coverage across all layers:
- **Foundation** (52 tests): Service providers, events, routes, response protocol, product model
- **Services** (38 tests): Business logic isolated from HTTP layer with full CRUD coverage
- **API** (86 tests): Full HTTP request/response cycle with validation and error handling

**All 176 tests are passing with 595 assertions**, providing confidence in the package's functionality and enabling safe refactoring. Tests run in isolated Laravel environment using Orchestra Testbench with SQLite in-memory database for speed and reliability.

**Key Testing Features:**
- ✅ Interface-based dependency injection testing
- ✅ Event system validation (WordPress-style hooks)
- ✅ ResponseProtocol standardization (`success()` and `failed()`)
- ✅ Comprehensive validation testing
- ✅ Relationship loading verification
- ✅ 404 and error response handling
- ✅ Model attribute casting and computed properties
- ✅ Hierarchical data structures (categories, taxonomies)
