# Product Package Seeder - Usage Guide

## Overview

The `ProductPackageSeeder.php` file contains comprehensive test data for the Arkenstone Product package. It seeds **8 tables** with realistic e-commerce data, perfect for frontend development and testing.

## What Gets Seeded

| Table | Records | Description |
|-------|---------|-------------|
| `brands` | 10 | Apple, Samsung, Sony, Dell, HP, Nike, Adidas, Canon, LG, Microsoft |
| `categories` | 15 | Hierarchical categories (Electronics, Clothing, etc.) |
| `taxonomy_types` | 5 | Color, Size, Material, Storage Capacity, RAM |
| `taxonomies` | 30 | Various colors, sizes, materials, and specs |
| `products` | 50 | Mixed electronics and clothing products |
| `product_images` | ~150-200 | 3-5 images per product (first is primary) |
| `product_categories` | ~70-80 | Product-category relationships |
| `product_taxonomies` | ~300-400 | Product-taxonomy relationships |

## Installation Steps

### 1. Copy the Seeder File

Copy `ProductPackageSeeder.php` to your Laravel project:

```bash
# From your Laravel project root
cp path/to/arkenstone-core/database/seeders/ProductPackageSeeder.php database/seeders/
```

### 2. Run Migrations First

Make sure all Product package migrations are run:

```bash
php artisan migrate --path=vendor/arkenstone/core/database/migrations
```

### 3. Run the Seeder

Execute the seeder to populate your database:

```bash
php artisan db:seed --class=ProductPackageSeeder
```

**Expected Output:**
```
Seeding Product Package data...
✓ Brands seeded (10 records)
✓ Categories seeded (15 records)
✓ Taxonomy Types seeded (5 records)
✓ Taxonomies seeded (30 records)
✓ Products seeded (50 records)
✓ Product Images seeded (~150-200 records)
✓ Product-Category relationships seeded
✓ Product-Taxonomy relationships seeded
🎉 Product Package seeding completed successfully!
```

### 4. Verify the Data

Check that data was seeded correctly:

```bash
# Count records in each table
php artisan tinker

>>> DB::table('brands')->count();
>>> DB::table('categories')->count();
>>> DB::table('products')->count();
>>> DB::table('product_images')->count();
```

## Sample Products

The seeder includes realistic products across multiple brands:

**Electronics:**
- iPhone 15 Pro Max ($1,199.99)
- MacBook Pro 16" M3 ($2,499.99)
- Galaxy S24 Ultra ($1,199.99)
- Dell XPS 15 ($1,799.99)
- PlayStation 5 ($499.99)
- Sony Alpha 7 IV Camera ($2,499.99)

**Clothing:**
- Nike Air Max 270 Sneakers ($149.99)
- Adidas Ultraboost 23 ($189.99)
- Nike Tech Fleece Joggers ($99.99)

## Category Hierarchy

The seeder creates hierarchical categories:

```
Electronics
├── Laptops
├── Smartphones
├── Cameras
└── Televisions

Clothing
├── Men's Clothing
├── Women's Clothing
└── Footwear

Home & Kitchen
├── Kitchen Appliances
└── Home Decor

Sports & Outdoors
├── Fitness Equipment
└── Outdoor Gear
```

## Taxonomy Examples

Products are tagged with relevant taxonomies:

**Colors:** Black, White, Silver, Gold, Blue, Red, Green  
**Sizes:** XS, S, M, L, XL, XXL  
**Materials:** Cotton, Polyester, Aluminum, Plastic, Glass  
**Storage:** 64GB, 128GB, 256GB, 512GB, 1TB  
**RAM:** 4GB, 8GB, 16GB, 32GB, 64GB

## Product Images

All products have 3-5 images using placeholder URLs:
- Format: `https://picsum.photos/seed/{unique-seed}/800/600`
- First image is always marked as `is_primary = true`
- Images have sequential `sort_order`

## Important Notes

### Idempotent Seeding

The seeder **truncates all tables** before seeding, so you can run it multiple times safely:

```bash
# Running this multiple times is safe
php artisan db:seed --class=ProductPackageSeeder
```

⚠️ **Warning:** This will delete all existing Product package data!

### Foreign Key Constraints

The seeder handles foreign keys properly:
- Disables checks before truncating
- Seeds tables in dependency order
- Re-enables checks after completion

### No Factory Dependencies

The seeder uses **direct DB inserts** (not Eloquent or factories) for speed and independence from the host application's factory setup.

## API Testing with Seeded Data

After seeding, you can test these API endpoints:

### Get Products with Filters

```bash
GET /api/products?search=iPhone&brand_ids[]=1&category_ids[]=2&min_price=1000
```

### Get Product by ID

```bash
GET /api/products/1
```

### Get Categories (Hierarchical)

```bash
GET /api/categories
```

### Get Brand Products

```bash
GET /api/brands/1/products
```

### Get Product Images

```bash
GET /api/products/1/images
```

## Customization

To customize the seeded data:

1. Open `ProductPackageSeeder.php`
2. Modify the arrays in methods:
   - `seedBrands()` - Add/change brands
   - `seedCategories()` - Modify category structure
   - `seedProducts()` - Add/change products
   - `seedTaxonomies()` - Add/change taxonomy values

## Troubleshooting

### "Class 'ProductPackageSeeder' not found"

Run Composer autoload:
```bash
composer dump-autoload
```

### Foreign Key Constraint Errors

Ensure migrations are run in the correct order:
```bash
php artisan migrate:fresh
php artisan db:seed --class=ProductPackageSeeder
```

### Images Not Loading

The seeder uses placeholder images from `picsum.photos`. Replace URLs with your actual image storage paths if needed.

## Support

For issues or questions, contact:
**Janith Nirmal** - Algowrite Software Solutions

---

**Created:** December 4, 2025  
**Package:** Arkenstone Core v1.0  
**Laravel:** 10+
