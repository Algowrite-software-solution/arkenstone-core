# Stock Module Seeder Usage Guide

This guide explains how to use the **StockModuleSeeder** to populate your database with sample stock data including suppliers, variants, variation options, stock records, and reservations.

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Installation](#installation)
4. [Running the Seeder](#running-the-seeder)
5. [What Data Gets Created](#what-data-gets-created)
6. [Database Reset](#database-reset)
7. [Troubleshooting](#troubleshooting)
8. [Customization](#customization)
9. [Production Considerations](#production-considerations)

---

## Overview

The **StockModuleSeeder** creates a complete stock management dataset for testing and development purposes. It generates realistic supplier information, product variants, stock records for all products, and sample reservations with various statuses.

**Key Features:**
- ✅ Creates 10 diverse suppliers with international locations
- ✅ Sets up 5 variant types (Size, Color, Material, Storage, Style) with 29 total options
- ✅ Generates stock records for ALL products from ProductPackageSeeder
- ✅ Intelligently assigns suppliers based on product type
- ✅ Adds relevant variant options to products (electronics get storage/color, clothing gets size/material)
- ✅ Creates 10 sample reservations with mixed statuses (pending, committed, expired, fulfilled)
- ✅ Wrapped in database transaction for data integrity
- ✅ Provides detailed summary output

---

## Prerequisites

### Required Seeders

The StockModuleSeeder **REQUIRES** the ProductPackageSeeder to be run first, as it creates stock records for existing products.

**Dependency Chain:**
```
1. ProductPackageSeeder (creates 50 products)
   ↓
2. StockModuleSeeder (creates stocks for those products)
```

### Database Requirements

Ensure the following migrations have been run:

```bash
# Stock Module Migrations
2025_12_12_000001_create_suppliers_table.php
2025_12_12_000002_create_variants_table.php
2025_12_12_000003_create_variation_options_table.php
2025_12_12_000004_create_stocks_table.php
2025_12_12_000005_create_stock_variant_options_table.php
2025_12_12_000006_create_stock_reservations_table.php

# Product Module Migrations (prerequisite)
2024_01_01_000005_create_products_table.php
```

Check migration status:
```bash
php artisan migrate:status
```

---

## Installation

The seeder is already included in the package. No additional installation steps are required.

**File Location:**
```
database/seeders/StockModuleSeeder.php
```

---

## Running the Seeder

### Step 1: Run Prerequisites

First, ensure you have products in the database:

```bash
php artisan db:seed --class=ProductPackageSeeder
```

**Expected Output:**
```
Seeding: ProductPackageSeeder
✓ 50 products created successfully
```

### Step 2: Run Stock Module Seeder

```bash
php artisan db:seed --class=StockModuleSeeder
```

**Expected Output:**
```
╔════════════════════════════════════════════════════════════╗
║        STOCK MODULE SEEDER - SUMMARY REPORT                ║
╚════════════════════════════════════════════════════════════╝

📦 SUPPLIERS
   ✓ Created: 10 suppliers
   └─ Types: Tech Distributors, Electronics, Fashion Wholesalers

📊 VARIANTS & OPTIONS
   ✓ Created: 5 variants
   ✓ Created: 29 variation options
   └─ Size (6), Color (8), Material (6), Storage (5), Style (4)

🏭 STOCK RECORDS
   ✓ Created: 50 stock records
   ✓ Total Inventory: 7,500 units
   └─ Average per product: 150 units

🔖 RESERVATIONS
   ✓ Created: 10 sample reservations
   └─ Status: 6 pending, 2 committed, 1 expired, 1 fulfilled

═══════════════════════════════════════════════════════════════
✅ Stock Module Seeder completed successfully!
═══════════════════════════════════════════════════════════════
```

### Step 3: Verify Data

Check the database tables:

```bash
# Count records
php artisan tinker
```

```php
\Arkenstone\Core\ECommerce\Stock\Models\Supplier::count(); // 10
\Arkenstone\Core\ECommerce\Stock\Models\Variant::count(); // 5
\Arkenstone\Core\ECommerce\Stock\Models\VariationOption::count(); // 29
\Arkenstone\Core\ECommerce\Stock\Models\Stock::count(); // 50
\Arkenstone\Core\ECommerce\Stock\Models\StockReservation::count(); // 10
```

---

## What Data Gets Created

### 1. Suppliers (10 Total)

| Supplier Code | Name | Location | Type |
|---------------|------|----------|------|
| SUP-TECH-001 | Tech Distributors International | San Jose, CA, USA | Technology |
| SUP-ELEC-001 | Global Electronics Supply Co. | Shenzhen, China | Electronics |
| SUP-FASH-001 | Premium Fashion Wholesalers | Milan, Italy | Fashion |
| SUP-SMRT-001 | Smart Device Importers Ltd. | London, UK | Tech Devices |
| SUP-PACF-001 | Pacific Rim Trading | Sydney, Australia | General |
| SUP-EURO-001 | European Electronics Group | Berlin, Germany | Electronics |
| SUP-SPRT-001 | SportGear Wholesale | Portland, OR, USA | Sports |
| SUP-ASIA-001 | Asia Direct Imports | Singapore | General |
| SUP-BDGT-001 | Budget Electronics Depot | Mumbai, India | Budget Tech |
| SUP-PREM-001 | Premium Tech Solutions | Tokyo, Japan | Premium Tech |

**Each supplier includes:**
- Complete address (street, city, state, country, postal code)
- Contact person name
- Email address
- Phone number
- Status (all set to "active")
- Optional notes

### 2. Variants (5 Types)

#### Size Variant
- **6 options:** Extra Small (XS), Small (S), Medium (M), Large (L), Extra Large (XL), 2XL
- **Meta data:** `code` (e.g., "S"), `sort` (1-6)

#### Color Variant
- **8 options:** Black, White, Red, Blue, Green, Silver, Gold, Space Gray
- **Meta data:** `hex` code (e.g., "#000000"), `rgb` array (e.g., [0, 0, 0])

#### Material Variant
- **6 options:** Cotton, Polyester, Leather, Mesh, Aluminum, Plastic
- **Meta data:** `type` (e.g., "fabric", "metal")

#### Storage Variant
- **5 options:** 64GB, 128GB, 256GB, 512GB, 1TB
- **Meta data:** `capacity_gb` (e.g., 64)

#### Style Variant
- **4 options:** Casual, Formal, Sport, Classic
- **Meta data:** None

### 3. Stock Records (50 Total)

For **each product** from ProductPackageSeeder, a stock record is created with:

| Field | How It's Generated |
|-------|-------------------|
| `product_id` | Links to existing product |
| `sku` | Format: `STK-{PREFIX}-{INDEX}-{HASH}` (e.g., "STK-LAP-0001-A4B2") |
| `barcode` | Random 13-digit EAN-13 barcode (or null) |
| `price` | Uses product's price |
| `cost` | Calculated as `price * 0.6` (60% of retail) |
| `weight` | Estimated based on product category (e.g., laptops: 1.5-3kg) |
| `quantity_on_hand` | Uses product's `quantity` field (typically 150) |
| `quantity_reserved` | Initially 0 |
| `min_stock_level` | Random between 10-30 |
| `supplier_id` | Intelligently selected based on product type |
| `image_url_id` | First image from product (if available) |
| `status` | "active" |

**Supplier Assignment Logic:**
- **Electronics (phones, laptops, tablets)** → Tech Distributors, Global Electronics, Smart Device Importers, European Electronics Group
- **Clothing (shirts, pants, jackets)** → Premium Fashion Wholesalers, SportGear Wholesale
- **Shoes** → SportGear Wholesale
- **Other products** → Pacific Rim Trading, Budget Electronics Depot, Asia Direct Imports

**Variant Option Assignment:**
- **Electronics** → Color + Storage (e.g., "Space Gray" + "256GB")
- **Clothing** → Size + Color + Material (e.g., "Large" + "Blue" + "Cotton")
- **Shoes** → Size + Color (e.g., "M" + "Black")
- **Others** → Random color

### 4. Stock Reservations (10 Total)

| Status | Count | Expires In | Description |
|--------|-------|------------|-------------|
| Pending | 6 | 5-15 minutes | Active cart reservations |
| Committed | 2 | 3 days | Orders placed, payment confirmed |
| Expired | 1 | Past | Cart abandoned |
| Fulfilled | 1 | None | Order shipped |

**Each reservation includes:**
- Random stock record
- Quantity: 1-3 units
- Reference type: "cart" (for pending) or "order" (for committed/fulfilled)
- Reference ID: Random ID (10000-99999)
- Expiry time based on status

---

## Database Reset

### Option 1: Fresh Migration + All Seeders

Reset the entire database and run all seeders:

```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Run all migrations
3. Run DatabaseSeeder (which should call ProductPackageSeeder and StockModuleSeeder)

### Option 2: Fresh Migration + Specific Seeders

Reset database and run only specific seeders:

```bash
# Reset database
php artisan migrate:fresh

# Run seeders in order
php artisan db:seed --class=ProductPackageSeeder
php artisan db:seed --class=StockModuleSeeder
```

### Option 3: Delete Stock Data Only

Remove only stock data without affecting products:

```bash
php artisan tinker
```

```php
// Delete in correct order (respects foreign keys)
\Arkenstone\Core\ECommerce\Stock\Models\StockReservation::truncate();
DB::table('stock_variant_options')->truncate();
\Arkenstone\Core\ECommerce\Stock\Models\Stock::truncate();
\Arkenstone\Core\ECommerce\Stock\Models\VariationOption::truncate();
\Arkenstone\Core\ECommerce\Stock\Models\Variant::truncate();
\Arkenstone\Core\ECommerce\Stock\Models\Supplier::truncate();

// Then re-run seeder
exit
php artisan db:seed --class=StockModuleSeeder
```

---

## Troubleshooting

### Error: "No products found"

**Problem:** ProductPackageSeeder hasn't been run.

**Solution:**
```bash
php artisan db:seed --class=ProductPackageSeeder
php artisan db:seed --class=StockModuleSeeder
```

---

### Error: "SQLSTATE[23000]: Integrity constraint violation"

**Problem:** Foreign key constraint failure (e.g., trying to create stock for non-existent product).

**Solution:**
```bash
# Reset and run in correct order
php artisan migrate:fresh
php artisan db:seed --class=ProductPackageSeeder
php artisan db:seed --class=StockModuleSeeder
```

---

### Error: "Duplicate entry for key 'sku'"

**Problem:** Running seeder multiple times without reset.

**Solution:**
```bash
# Option A: Delete existing stock data
php artisan tinker
\Arkenstone\Core\ECommerce\Stock\Models\Stock::truncate();
exit

# Option B: Fresh migration
php artisan migrate:fresh --seed
```

---

### Error: "Call to a member function count() on null"

**Problem:** Product model relationship not loading correctly.

**Solution:**
1. Check that Product model exists: `\Arkenstone\Core\ECommerce\Product\Models\Product`
2. Verify products table has data: `php artisan tinker → Product::count();`
3. Ensure migrations are up to date: `php artisan migrate:status`

---

### No Output After Running Seeder

**Problem:** Seeder ran but no summary displayed.

**Check:**
```bash
# Verify data was created
php artisan tinker
\Arkenstone\Core\ECommerce\Stock\Models\Stock::count();
```

If count is > 0, seeder worked but output was suppressed. Check terminal settings.

---

### Transaction Rollback Errors

**Problem:** Seeder fails mid-execution and rolls back.

**Debug:**
1. Check error message for specific failure point
2. Verify all required tables exist: `php artisan migrate:status`
3. Check database user has sufficient privileges
4. Look for validation errors in seeder logic

**Solution:**
```bash
# Enable detailed error output
php artisan db:seed --class=StockModuleSeeder --verbose
```

---

## Customization

### Modify Number of Suppliers

Edit `createSuppliers()` method in [StockModuleSeeder.php](database/seeders/StockModuleSeeder.php):

```php
private function createSuppliers(): void
{
    $suppliers = [
        // Add or remove supplier definitions here
    ];
    
    foreach ($suppliers as $data) {
        Supplier::create($data);
    }
}
```

### Add Custom Variants

Edit `createVariantsAndOptions()` method:

```php
// Example: Add "Warranty" variant
$warrantyVariant = Variant::create(['name' => 'Warranty']);
VariationOption::create([
    'variant_id' => $warrantyVariant->id,
    'name' => '1 Year',
    'meta' => ['duration_months' => 12]
]);
```

### Change Stock Quantities

Edit quantity ranges in `createStocksForProducts()`:

```php
'quantity_on_hand' => $product->quantity ?? rand(50, 200), // Change range
'min_stock_level' => rand(5, 15), // Change threshold
```

### Adjust Reservation Mix

Edit `createSampleReservations()` method:

```php
// Change number of reservations per status
$pendingCount = 8; // Instead of 6
$committedCount = 4; // Instead of 2
```

---

## Production Considerations

### ⚠️ DO NOT USE IN PRODUCTION

This seeder is designed for **development and testing only**. It creates sample data with:
- Generic supplier information
- Random SKUs and barcodes
- Mock reservation data

### For Production Use

1. **Create Real Supplier Records:**
   - Use actual supplier information
   - Obtain real supplier codes
   - Verify contact details

2. **Import Real Stock Data:**
   - Import from CSV/Excel files
   - Integrate with supplier APIs
   - Use barcode scanning for accuracy

3. **Set Actual Quantities:**
   - Conduct physical inventory counts
   - Set realistic min_stock_levels based on sales data
   - Configure proper cost and pricing

4. **Disable Seeder in Production:**
   ```php
   // In DatabaseSeeder.php
   if (app()->environment('local', 'testing')) {
       $this->call(StockModuleSeeder::class);
   }
   ```

5. **Use Migrations Only:**
   ```bash
   # Production deployment
   php artisan migrate --force
   # DO NOT run: php artisan db:seed
   ```

---

## Testing with Seeder Data

### API Testing

After running the seeder, test the Stock API endpoints:

```bash
# Get all stocks
curl http://localhost/api/v1/stocks

# Get low stock items
curl http://localhost/api/v1/stocks/low-stock

# Create a reservation
curl -X POST http://localhost/api/v1/stock-reservations/reserve \
  -H "Content-Type: application/json" \
  -d '{"stock_id": 1, "quantity": 2, "reference_type": "cart", "reference_id": 12345}'
```

### PHPUnit Tests

Use seeder data in feature tests:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductPackageSeeder::class);
        $this->seed(StockModuleSeeder::class);
    }
    
    public function test_can_list_stocks()
    {
        $response = $this->getJson('/api/v1/stocks');
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'data' => [
                'data' => [
                    '*' => ['id', 'sku', 'quantity_on_hand']
                ]
            ]
        ]);
    }
}
```

---

## Related Documentation

- **[STOCK_API_DOCUMENTATION.md](STOCK_API_DOCUMENTATION.md)** - Complete API endpoint reference
- **[SEEDER_USAGE.md](SEEDER_USAGE.md)** - General seeder usage guide (Product Module)
- **[LOCAL_DEVELOPMENT_GUIDE.md](LOCAL_DEVELOPMENT_GUIDE.md)** - Local development setup
- **[TESTING.md](TESTING.md)** - Testing guide

---

## Support

For issues or questions about the Stock Module Seeder:

1. Check this documentation
2. Review error messages in terminal
3. Verify database state with `php artisan tinker`
4. Check migration status with `php artisan migrate:status`
5. Contact the development team

---

**Last Updated:** December 16, 2025  
**Seeder Version:** 1.0.0
