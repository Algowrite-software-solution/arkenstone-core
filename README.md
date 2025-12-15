# Arkenstone Core
Laravel based E Commerce Feature targetted Web Application.

## 📚 Documentation

- **[API Documentation](./document/API_DOCUMENTATION.md)** - Complete API endpoint reference
- **[Backend Features Plan](./document/BACKEND_FEATURES_PLAN.md)** - Comprehensive feature planning (13 modules)
- **[Feature Roadmap](./document/FEATURE_ROADMAP.md)** - Visual timeline and phase breakdown
- **[Implementation Guide](./document/ORDER_STOCK_IMPLEMENTATION_GUIDE.md)** - Code examples for Order & Stock modules
- **[Executive Summary](./document/BACKEND_FEATURES_SUMMARY.md)** - High-level overview for stakeholders
- **[Testing Guide](./document/TESTING.md)** - Test setup and guidelines
- **[Local Development Guide](./document/LOCAL_DEVELOPMENT_GUIDE.md)** - Development environment setup
- **[Seeder Usage](./document/SEEDER_USAGE.md)** - Database seeding instructions

## Current Status

**Version:** 0.2.0  
**Status:** Product Management Module Complete ✅

**Implemented Features:**
- ✅ Product CRUD with advanced filtering
- ✅ Brand management
- ✅ Category management (hierarchical)
- ✅ Taxonomy system (flexible attributes)
- ✅ Product image management
- ✅ Test coverage: 176 tests, 595 assertions

**Planned Features:** (See [Backend Features Plan](./document/BACKEND_FEATURES_PLAN.md))
- 🚧 Order Management System
- 🚧 Stock/Inventory Management
- 🚧 Customer Management
- 🚧 Cart & Checkout
- 🚧 Payment Gateway Integration
- 🚧 And 8 more modules...

##  Requirements

- PHP >= 8.2
- Laravel >= 10.0 (automatically handled via Orchestra Testbench)

## Installation

### 1. Install via Composer

```bash
composer require arkenstone/core
```

### 2. Publish Configuration (Recommended for production )

```bash
php artisan vendor:publish --tag=arkenstone-config
```

This creates `config/arkenstone.php` in your Laravel app.

### 3. Publish Migrations (Recommended for production )

```bash
php artisan vendor:publish --tag=arkenstone-migrations
```

This copies all migration files to `database/migrations/` in your Laravel app.

### 4. Publish everything Just one command (Recommended for development)

# Publish everything

```bash
php artisan vendor:publish --provider="Arkenstone\Core\CoreServiceProvider"
```

# Or by tag
```bash
php artisan vendor:publish --tag=arkenstone
```
### 5. Run Migrations

```bash
php artisan migrate
```

This creates the following tables:
- `brands`
- `categories`
- `taxonomy_types`
- `taxonomies`
- `products`
- `product_images`
- `product_categories`
- `product_taxonomies`

### 6. Configure Environment Variables

Add to your `.env`:

```env
ARKENSTONE_CORE_ENABLED=true
ARKENSTONE_CORE_PREFIX=api/v1
ARKENSTONE_IMAGE_DISK=public
ARKENSTONE_IMAGE_PATH=products/images
ARKENSTONE_IMAGE_MAX_SIZE=5120
```

### 7. Setup Storage

```bash
php artisan storage:link
```

