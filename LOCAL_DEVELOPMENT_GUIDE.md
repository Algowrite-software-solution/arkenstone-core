# Local Development & Testing Guide

This guide explains how to set up and test the Arkenstone Core package locally using a symlinked Laravel application.

---

## 📋 Option 2: Local Path Repository Setup

This is the recommended approach for active package development. It creates a symlink between your package and a test Laravel application, allowing instant code changes without reinstalling.

---

## 🎯 Setup Configuration Plan

### **Step 1: Prepare Directory Structure**

Create a parent directory to hold both projects:

```bash
# Create workspace directory
mkdir arkenstone-workspace
cd arkenstone-workspace

# Your package should already exist here
# Move or clone your package if needed
git clone https://github.com/YOUR-USERNAME/arkenstone-core.git

# Create fresh Laravel test project
composer create-project laravel/laravel arkenstone-test-app
```

**Final structure:**
```
arkenstone-workspace/
  ├── arkenstone-core/          # Your package repository
  └── arkenstone-test-app/      # Test Laravel application
```

---

### **Step 2: Configure Laravel Project's `composer.json`**

Navigate to test app and update `composer.json`:

```bash
cd arkenstone-test-app
```

Add this configuration:

```json
{
    "name": "laravel/laravel",
    "type": "project",
    "description": "Testing Arkenstone Core Package",
    "repositories": [
        {
            "type": "path",
            "url": "../arkenstone-core",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "arkenstone/core": "@dev"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0.1"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

**Key points:**
- `"type": "path"` - Uses local filesystem
- `"symlink": true"` - Changes in package reflect immediately (no reinstall needed)
- `"arkenstone/core": "@dev"` - Uses development version (accepts any commit)

---

### **Step 3: Install Package**

```bash
# Install the package
composer update arkenstone/core

# Verify installation
composer show arkenstone/core
```

You should see output like:
```
name     : arkenstone/core
descrip. : A Laravel package for e-commerce functionality
versions : * dev-main
```

---

### **Step 4: Publish Package Assets**

```bash
# Publish configuration
php artisan vendor:publish --provider="Arkenstone\Core\CoreServiceProvider" --tag=arkenstone-config

# Publish migrations
php artisan vendor:publish --provider="Arkenstone\Core\CoreServiceProvider" --tag=arkenstone-migrations

# Or publish everything at once
php artisan vendor:publish --provider="Arkenstone\Core\CoreServiceProvider"
```

This creates:
- `config/arkenstone.php` - Package configuration
- `database/migrations` - All product/category/taxonomy tables

---

### **Step 5: Configure Environment**

Update `.env` file:

```env
APP_NAME="Arkenstone Test"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arkenstone_test
DB_USERNAME=root
DB_PASSWORD=

# Arkenstone Configuration
ARKENSTONE_CORE_ENABLED=true
ARKENSTONE_CORE_PREFIX=api/v1
ARKENSTONE_IMAGE_DISK=public
ARKENSTONE_IMAGE_PATH=products/images
ARKENSTONE_IMAGE_MAX_SIZE=5120
ARKENSTONE_IMAGE_OPTIMIZE=true
```

---

### **Step 6: Setup Database & Storage**

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE arkenstone_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Setup storage symlink
php artisan storage:link

# Verify storage directory exists
mkdir -p storage/app/public/products/images
```

---

### **Step 7: Verify Package Discovery**

Check if the package is auto-discovered:

```bash
php artisan package:discover

# Should output:
# Discovered Package: arkenstone/core
```

Verify routes are loaded:

```bash
php artisan route:list --path=api/v1

# Should show routes like:
# POST   api/v1/products
# GET    api/v1/products/{product}
# POST   api/v1/products/{productId}/images/upload
```

---

### **Step 8: Create Test Controller (Optional)**

Create a quick test controller to verify functionality:

```php
<?php

namespace App\Http\Controllers;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\Request;

class ArkenstoneTestController extends Controller
{
    public function testProductService()
    {
        // Test service container binding
        $productService = app()->make('product');
        
        $products = $productService->getProducts([
            'is_active' => true,
            'per_page' => 5
        ]);

        return ResponseProtocol::success($products, 'Products retrieved successfully');
    }

    public function testCreateProduct(Request $request)
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-' . time(),
            'price' => 99.99,
            'stock_quantity' => 10,
            'is_active' => true,
            'minified_description' => 'A test product for Arkenstone',
            'details' => [
                'specifications' => ['color' => 'black', 'weight' => '500g'],
                'features' => ['Feature 1', 'Feature 2'],
                'warranty' => '1 year'
            ]
        ]);

        return ResponseProtocol::success($product, 'Product created successfully', 201);
    }
}
```

Add routes in `routes/web.php`:

```php
use App\Http\Controllers\ArkenstoneTestController;

Route::get('/test/products', [ArkenstoneTestController::class, 'testProductService']);
Route::post('/test/products/create', [ArkenstoneTestController::class, 'testCreateProduct']);
```

---

### **Step 9: Start Development Server**

```bash
php artisan serve

# Server started: http://127.0.0.1:8000
```

---

### **Step 10: Test API Endpoints**

#### Test 1: Get Products
```bash
curl http://localhost:8000/api/v1/products
```

#### Test 2: Create Product with New Fields
```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "MacBook Pro",
    "sku": "MBP-2024-001",
    "price": 1299.99,
    "stock_quantity": 5,
    "minified_description": "Powerful laptop for professionals",
    "details": {
      "specifications": {
        "cpu": "M3 Pro",
        "ram": "18GB",
        "storage": "512GB SSD"
      },
      "features": ["Bluetooth 5.3", "WiFi 6E", "Thunderbolt 4"],
      "warranty": "2 years AppleCare"
    }
  }'
```

#### Test 3: Upload Images
```bash
curl -X POST http://localhost:8000/api/v1/products/1/images/upload \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.png" \
  -F "alt_texts[]=Front view" \
  -F "alt_texts[]=Back view" \
  -F "primary_index=0"
```

#### Test 4: Create Product with Images
```bash
curl -X POST http://localhost:8000/api/v1/products \
  -F "name=iPhone 15 Pro" \
  -F "sku=IP15P-001" \
  -F "price=999.99" \
  -F "stock_quantity=20" \
  -F "minified_description=Latest iPhone model" \
  -F 'details={"specifications":{"chip":"A17 Pro","display":"6.1 inch"}}' \
  -F "uploaded_images[]=@/path/to/phone-front.jpg" \
  -F "uploaded_images[]=@/path/to/phone-back.jpg" \
  -F "image_alt_texts[]=Front" \
  -F "image_alt_texts[]=Back" \
  -F "primary_image_index=0"
```

---

## 🧪 Testing Your Code Changes - Step-by-Step Guide

---

## 🔄 **Quick Testing Workflow** (Most Common)

### Step 1: Make Changes in Package
```bash
cd arkenstone-workspace/arkenstone-core

# Edit your files (e.g., add new feature)
# Changes are INSTANTLY available via symlink!
```

### Step 2: Test Immediately in Laravel App
```bash
cd ../arkenstone-test-app

# Changes already active - just test the endpoint
curl http://localhost:8000/api/v1/products

# Or visit in browser
php artisan serve
# Visit: http://localhost:8000/api/v1/products
```

**✨ No `composer update` needed!** Symlink makes changes instant.

---

## 🎯 **Comprehensive Testing Checklist**

### **Level 1: Package Unit Tests** (Isolated)

Test your package logic **without** Laravel app:

```bash
cd arkenstone-workspace/arkenstone-core

# Run all package tests
composer test

# Or use PHPUnit directly
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Feature/API/ProductImageUploadTest.php

# Run specific test method
vendor/bin/phpunit --filter testUploadMultipleImages
```

**When to use:**
- ✅ Testing service methods (ProductService, etc.)
- ✅ Testing model scopes and relationships
- ✅ Testing validation rules
- ✅ After modifying business logic

---

### **Level 2: Integration Testing** (In Laravel App)

Test how your package works **inside** a real Laravel application:

#### Option A: Create Test Controller (Recommended)

```php
<?php

namespace App\Http\Controllers;

use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\Request;

class TestArkenstoneController extends Controller
{
    // Test new minified_description field
    public function testNewFields()
    {
        $productService = app()->make('product');
        
        $product = $productService->create([
            'name' => 'Test Product',
            'sku' => 'TEST-' . time(),
            'price' => 99.99,
            'minified_description' => 'Short description here',
            'details' => [
                'specifications' => ['cpu' => 'Intel i7'],
                'features' => ['Feature 1', 'Feature 2']
            ]
        ]);

        return ResponseProtocol::success($product, 'Test passed!');
    }

    // Test image upload
    public function testImageUpload(Request $request)
    {
        $productService = app()->make('product');
        
        $product = $productService->create([
            'name' => 'Product with Images',
            'sku' => 'IMG-' . time(),
            'price' => 199.99,
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            $productService->addImages($product->id, [
                'images' => $request->file('images'),
                'alt_texts' => $request->input('alt_texts', []),
            ]);
        }

        $product->load('images');
        return ResponseProtocol::success($product);
    }
}
```

Add routes:

```php
use App\Http\Controllers\TestArkenstoneController;

Route::get('/test/new-fields', [TestArkenstoneController::class, 'testNewFields']);
Route::post('/test/image-upload', [TestArkenstoneController::class, 'testImageUpload']);
```

Test it:

```bash
cd arkenstone-test-app
php artisan serve

# Test in browser or curl
curl http://localhost:8000/test/new-fields
```

#### Option B: Use Tinker (Quick Testing)

```bash
cd arkenstone-test-app
php artisan tinker
```

```php
// Test ProductService
$service = app()->make('product');

// Test new fields
$product = \Arkenstone\Core\ECommerce\Product\Models\Product::create([
    'name' => 'Test Product',
    'sku' => 'TEST-001',
    'price' => 99.99,
    'minified_description' => 'Short desc',
    'details' => ['spec' => 'value']
]);

dd($product->toArray());

// Test image upload
$product->images()->create([
    'image_url' => 'products/images/test.jpg',
    'alt_text' => 'Test image',
    'is_primary' => true
]);

// Verify relative URL format
$product->load('images');
dd($product->images->first()->image_url); // Should be: products/images/test.jpg
```

#### Option C: API Testing with Postman/Insomnia

1. **Import Collection** or manually create requests:

**Test 1: Create Product with New Fields**
```
POST http://localhost:8000/api/v1/products
Content-Type: application/json

{
  "name": "MacBook Pro",
  "sku": "MBP-001",
  "price": 1299.99,
  "minified_description": "Powerful laptop for professionals",
  "details": {
    "specifications": {
      "cpu": "M3 Pro",
      "ram": "18GB"
    },
    "features": ["Bluetooth", "WiFi 6E"],
    "warranty": "2 years"
  }
}
```

**Test 2: Upload Images**
```
POST http://localhost:8000/api/v1/products/1/images/upload
Content-Type: multipart/form-data

images[]: [file1.jpg]
images[]: [file2.png]
alt_texts[]: Front view
alt_texts[]: Back view
primary_index: 0
```

**Test 3: Create Product with Images**
```
POST http://localhost:8000/api/v1/products
Content-Type: multipart/form-data

name: iPhone 15
sku: IP15-001
price: 999.99
minified_description: Latest iPhone
details: {"chip":"A17 Pro"}
uploaded_images[]: [image1.jpg]
image_alt_texts[]: Front
primary_image_index: 0
```

---

### **Level 3: Manual Verification**

#### Check Database

```bash
cd arkenstone-test-app
php artisan tinker
```

```php
// Verify new fields exist
\Arkenstone\Core\ECommerce\Product\Models\Product::first();

// Check image URLs format
$product = \Arkenstone\Core\ECommerce\Product\Models\Product::with('images')->first();
$product->images->pluck('image_url'); // Should NOT have http://
```

Or use SQL:

```bash
mysql -u root -p arkenstone_test

SELECT id, name, minified_description, details FROM products LIMIT 5;
SELECT id, product_id, image_url FROM product_images LIMIT 5;
```

#### Check File Storage

```bash
cd arkenstone-test-app

# List uploaded images
ls -la storage/app/public/products/images/

# Verify symlink exists
ls -la public/storage  # Should point to ../storage/app/public
```

#### Check API Responses

```bash
# Verify image URLs are relative (not absolute)
curl http://localhost:8000/api/v1/products/1 | jq '.data.images[].image_url'
# Expected: "storage/products/images/abc123.jpg"
# NOT: "http://localhost:8000/storage/products/images/abc123.jpg"

# Verify new fields appear
curl http://localhost:8000/api/v1/products/1 | jq '.data | {minified_description, details}'
```

---

## 🔄 **When You Need `composer update`**

Only update the package when you:

### 1. Modified `composer.json` in Package

```bash
cd arkenstone-core
# After adding new dependency or changing autoload

cd ../arkenstone-test-app
composer update arkenstone/core
```

### 2. Added New Service Provider

```bash
cd arkenstone-test-app

# Clear Laravel cache
php artisan config:clear
php artisan cache:clear
php artisan package:discover
```

### 3. Added New Routes

```bash
cd arkenstone-test-app

# Clear route cache
php artisan route:clear
php artisan route:list --path=api/v1  # Verify new routes
```

### 4. Modified Migrations

```bash
cd arkenstone-test-app

# If you published migrations, re-publish
php artisan vendor:publish --tag=arkenstone-migrations --force
php artisan migrate:fresh  # Re-run all migrations
```

---

## 🐛 **Debugging Workflow**

If something doesn't work:

### Step 1: Verify Symlink is Active

```bash
cd arkenstone-test-app
ls -la vendor/arkenstone/core

# Should show: vendor/arkenstone/core -> ../../../arkenstone-core
```

### Step 2: Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Step 3: Check Package is Loaded

```bash
php artisan package:discover

# Should output:
# Discovered Package: arkenstone/core
```

### Step 4: Verify Routes Exist

```bash
php artisan route:list --path=api/v1

# Should show your new routes
```

### Step 5: Check Logs

```bash
tail -f storage/logs/laravel.log

# Make API request in another terminal
curl http://localhost:8000/api/v1/products
```

### Step 6: Test Service Container

```bash
php artisan tinker
```

```php
// Verify service is bound
app()->make('product'); // Should return ProductService instance

// Check event system
\Arkenstone\Core\Support\Event::dispatcher(); // Should not be null
```

---

## ✅ **Recommended Testing Flow for Your Changes**

Based on your recent improvements, follow this order:

### 1. Test New Product Fields
```bash
# Package test
cd arkenstone-core
vendor/bin/phpunit tests/Feature/API/ProductWithImagesTest.php

# Integration test
cd ../arkenstone-test-app
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","sku":"T1","price":99,"minified_description":"Short","details":{"spec":"value"}}'
```

### 2. Test Image Upload (Separate Endpoint)
```bash
curl -X POST http://localhost:8000/api/v1/products/1/images/upload \
  -F "images[]=@/path/to/image.jpg" \
  -F "alt_texts[]=Test image"
```

### 3. Test Image Upload During Product Creation
```bash
curl -X POST http://localhost:8000/api/v1/products \
  -F "name=Product with Image" \
  -F "sku=IMG-001" \
  -F "price=199" \
  -F "uploaded_images[]=@/path/to/image.jpg" \
  -F "image_alt_texts[]=Front view"
```

### 4. Test Image URL Format
```bash
# Should return relative paths
curl http://localhost:8000/api/v1/products/1 | jq '.data.images[].image_url'
# Expected: "storage/products/images/xyz.jpg"
```

### 5. Test Image Deletion
```bash
curl -X PUT http://localhost:8000/api/v1/products/1 \
  -F "name=Updated Product" \
  -F "delete_image_ids[]=5"
```

### 6. Run Full Package Test Suite
```bash
cd arkenstone-core
composer test
```

---

## 📊 **Testing Checklist Template**

Use this after making changes:

### Package Tests
- [ ] Run `composer test` in package directory
- [ ] All tests pass (check for 0 failures)
- [ ] No deprecation warnings

### Integration Tests  
- [ ] Clear Laravel caches (`php artisan optimize:clear`)
- [ ] Test API endpoints with curl/Postman
- [ ] Check response format matches documentation
- [ ] Verify database records created correctly
- [ ] Check uploaded files exist in storage

### Manual Verification
- [ ] New fields appear in API responses
- [ ] Image URLs are relative (not absolute)
- [ ] File uploads save to correct directory
- [ ] Validation errors return proper format
- [ ] Events fire correctly (check logs if listening)

### Documentation
- [ ] API_DOCUMENTATION.md updated
- [ ] README updated (if public API changed)
- [ ] Commit message describes changes

---

## 🚀 **Quick Reference Commands**

```bash
# Most used commands during development
cd arkenstone-core && composer test          # Test package
cd arkenstone-test-app && php artisan serve  # Start server
php artisan optimize:clear                   # Clear all caches
php artisan route:list --path=api/v1         # Show routes
tail -f storage/logs/laravel.log             # Watch logs
```

---

## 🔍 **Troubleshooting Common Issues**

### Issue: "Package not found"
```bash
# Clear Composer cache
composer clear-cache
composer update arkenstone/core
```

### Issue: Routes not loading
```bash
# Rediscover packages
php artisan package:discover
php artisan optimize:clear
```

### Issue: Database connection error
```bash
# Verify .env settings
php artisan config:clear
php artisan migrate:fresh
```

### Issue: Storage not working
```bash
# Recreate symlink
rm public/storage
php artisan storage:link
chmod -R 775 storage/
```

### Issue: Changes not reflecting
```bash
# If symlink isn't working, reinstall
composer remove arkenstone/core
composer require arkenstone/core:@dev
```

---

## 🎯 **Verification Checklist**

After setup, verify:

- [ ] `composer show arkenstone/core` shows package as installed
- [ ] `config/arkenstone.php` exists
- [ ] `php artisan route:list` shows `/api/v1/products` routes
- [ ] Database has `products`, `product_images`, `brands`, `categories` tables
- [ ] `storage/app/public` is symlinked to `public/storage`
- [ ] API endpoint `GET /api/v1/products` returns JSON response
- [ ] Can create product with `minified_description` and `details` fields
- [ ] Can upload images to `storage/app/public/products/images/`
- [ ] Image URLs in responses are relative: `storage/products/images/...`

---

## 📝 **Next Steps**

Once setup is complete:

1. ✅ Test all CRUD operations for products
2. ✅ Test image upload (separate endpoint + during creation)
3. ✅ Verify `minified_description` and `details` fields work
4. ✅ Test image deletion during product update
5. ✅ Verify relative image URLs in responses
6. ✅ Test with Postman/Insomnia for complex scenarios
7. ✅ Write integration tests in the Laravel app
8. ✅ Test taxonomy/category associations

---

This setup gives you **instant feedback** for development. Any code changes in `arkenstone-core/` will immediately be available in your test Laravel app! 🚀
