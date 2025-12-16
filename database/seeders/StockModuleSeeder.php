<?php

namespace Database\Seeders;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Arkenstone\Core\ECommerce\Stock\Models\StockReservation;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Arkenstone\Core\ECommerce\Stock\Models\VariationOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Starting Stock Module Seeding...');

            // Step 1: Create Suppliers
            $suppliers = $this->createSuppliers();
            $this->command->info('✓ Created ' . count($suppliers) . ' suppliers');

            // Step 2: Create Variants and Options
            [$variants, $variantOptions] = $this->createVariantsAndOptions();
            $this->command->info('✓ Created ' . count($variants) . ' variants with ' . $variantOptions->count() . ' options');

            // Step 3: Create Stock records for all products
            $stocks = $this->createStocksForProducts($suppliers, $variantOptions);
            $this->command->info('✓ Created ' . count($stocks) . ' stock records');

            // Step 4: Create sample reservations
            $reservations = $this->createSampleReservations($stocks);
            $this->command->info('✓ Created ' . count($reservations) . ' sample reservations');

            $this->command->info('✅ Stock Module Seeding Completed Successfully!');
            $this->printSummary($suppliers, $variants, $stocks, $reservations);
        });
    }

    /**
     * Create supplier records.
     */
    private function createSuppliers(): array
    {
        $supplierData = [
            [
                'name' => 'Tech Distributors International',
                'contact_person' => 'Michael Chen',
                'email' => 'orders@techdist.com',
                'phone' => '+1-555-0101',
                'address' => '1500 Technology Drive',
                'city' => 'San Jose',
                'state' => 'California',
                'country' => 'USA',
                'postal_code' => '95110',
                'supplier_code' => 'SUP-TECH-001',
                'status' => 'active',
                'notes' => 'Primary supplier for electronics and computer hardware',
            ],
            [
                'name' => 'Global Electronics Supply Co.',
                'contact_person' => 'Sarah Johnson',
                'email' => 'procurement@globalsupply.com',
                'phone' => '+1-555-0202',
                'address' => '2800 Commerce Boulevard',
                'city' => 'Austin',
                'state' => 'Texas',
                'country' => 'USA',
                'postal_code' => '78701',
                'supplier_code' => 'SUP-ELEC-002',
                'status' => 'active',
                'notes' => 'Specializes in consumer electronics and accessories',
            ],
            [
                'name' => 'Premium Fashion Wholesalers',
                'contact_person' => 'David Martinez',
                'email' => 'sales@fashionwholesale.com',
                'phone' => '+1-555-0303',
                'address' => '750 Fashion Avenue',
                'city' => 'New York',
                'state' => 'New York',
                'country' => 'USA',
                'postal_code' => '10018',
                'supplier_code' => 'SUP-FASH-003',
                'status' => 'active',
                'notes' => 'Clothing and footwear supplier with fast shipping',
            ],
            [
                'name' => 'Smart Device Importers Ltd.',
                'contact_person' => 'Emma Wilson',
                'email' => 'info@smartdevices.com',
                'phone' => '+44-20-5555-0404',
                'address' => '45 Innovation Park',
                'city' => 'London',
                'state' => 'England',
                'country' => 'United Kingdom',
                'postal_code' => 'SW1A 1AA',
                'supplier_code' => 'SUP-SMRT-004',
                'status' => 'active',
                'notes' => 'UK-based importer of smartphones and tablets',
            ],
            [
                'name' => 'Pacific Rim Trading Company',
                'contact_person' => 'James Lee',
                'email' => 'orders@pacificrim.com',
                'phone' => '+1-555-0505',
                'address' => '8900 Harbor View Road',
                'city' => 'Seattle',
                'state' => 'Washington',
                'country' => 'USA',
                'postal_code' => '98101',
                'supplier_code' => 'SUP-PACF-005',
                'status' => 'active',
                'notes' => 'General merchandise and multi-category supplier',
            ],
            [
                'name' => 'European Electronics Group',
                'contact_person' => 'Hans Mueller',
                'email' => 'contact@euroelectronics.de',
                'phone' => '+49-30-5555-0606',
                'address' => 'Hauptstrasse 123',
                'city' => 'Berlin',
                'state' => 'Berlin',
                'country' => 'Germany',
                'postal_code' => '10115',
                'supplier_code' => 'SUP-EURO-006',
                'status' => 'active',
                'notes' => 'High-end electronics and computer peripherals',
            ],
            [
                'name' => 'SportGear Wholesale Network',
                'contact_person' => 'Amanda Rodriguez',
                'email' => 'wholesale@sportgear.com',
                'phone' => '+1-555-0707',
                'address' => '3200 Athletic Center',
                'city' => 'Portland',
                'state' => 'Oregon',
                'country' => 'USA',
                'postal_code' => '97201',
                'supplier_code' => 'SUP-SPRT-007',
                'status' => 'active',
                'notes' => 'Athletic footwear and sportswear specialist',
            ],
            [
                'name' => 'Asia Direct Imports',
                'contact_person' => 'Kenji Tanaka',
                'email' => 'imports@asiadirect.jp',
                'phone' => '+81-3-5555-0808',
                'address' => '7-8-9 Shibuya',
                'city' => 'Tokyo',
                'state' => 'Tokyo',
                'country' => 'Japan',
                'postal_code' => '150-0001',
                'supplier_code' => 'SUP-ASIA-008',
                'status' => 'active',
                'notes' => 'Direct importer from Asian manufacturers',
            ],
            [
                'name' => 'Budget Electronics Depot',
                'contact_person' => 'Robert Thompson',
                'email' => 'sales@budgetdepot.com',
                'phone' => '+1-555-0909',
                'address' => '5500 Warehouse District',
                'city' => 'Chicago',
                'state' => 'Illinois',
                'country' => 'USA',
                'postal_code' => '60601',
                'supplier_code' => 'SUP-BDGT-009',
                'status' => 'active',
                'notes' => 'Cost-effective supplier for budget electronics',
            ],
            [
                'name' => 'Premium Tech Solutions',
                'contact_person' => 'Victoria Chang',
                'email' => 'premium@techsolutions.com',
                'phone' => '+1-555-1010',
                'address' => '1200 Silicon Valley Way',
                'city' => 'Palo Alto',
                'state' => 'California',
                'country' => 'USA',
                'postal_code' => '94301',
                'supplier_code' => 'SUP-PREM-010',
                'status' => 'active',
                'notes' => 'High-end technology and premium brand products',
            ],
        ];

        $suppliers = [];
        foreach ($supplierData as $data) {
            $suppliers[] = Supplier::create($data);
        }

        return $suppliers;
    }

    /**
     * Create variants and their options.
     */
    private function createVariantsAndOptions(): array
    {
        // Create Variants
        $sizeVariant = Variant::create(['name' => 'Size']);
        $colorVariant = Variant::create(['name' => 'Color']);
        $materialVariant = Variant::create(['name' => 'Material']);
        $storageVariant = Variant::create(['name' => 'Storage']);
        $styleVariant = Variant::create(['name' => 'Style']);

        $variants = [
            'size' => $sizeVariant,
            'color' => $colorVariant,
            'material' => $materialVariant,
            'storage' => $storageVariant,
            'style' => $styleVariant,
        ];

        // Create Size Options
        $sizeOptions = [
            ['variant_id' => $sizeVariant->id, 'name' => 'Extra Small (XS)', 'meta' => ['code' => 'XS', 'sort' => 1]],
            ['variant_id' => $sizeVariant->id, 'name' => 'Small (S)', 'meta' => ['code' => 'S', 'sort' => 2]],
            ['variant_id' => $sizeVariant->id, 'name' => 'Medium (M)', 'meta' => ['code' => 'M', 'sort' => 3]],
            ['variant_id' => $sizeVariant->id, 'name' => 'Large (L)', 'meta' => ['code' => 'L', 'sort' => 4]],
            ['variant_id' => $sizeVariant->id, 'name' => 'Extra Large (XL)', 'meta' => ['code' => 'XL', 'sort' => 5]],
            ['variant_id' => $sizeVariant->id, 'name' => '2XL', 'meta' => ['code' => '2XL', 'sort' => 6]],
        ];

        // Create Color Options
        $colorOptions = [
            ['variant_id' => $colorVariant->id, 'name' => 'Black', 'meta' => ['hex' => '#000000', 'rgb' => [0, 0, 0]]],
            ['variant_id' => $colorVariant->id, 'name' => 'White', 'meta' => ['hex' => '#FFFFFF', 'rgb' => [255, 255, 255]]],
            ['variant_id' => $colorVariant->id, 'name' => 'Red', 'meta' => ['hex' => '#FF0000', 'rgb' => [255, 0, 0]]],
            ['variant_id' => $colorVariant->id, 'name' => 'Blue', 'meta' => ['hex' => '#0000FF', 'rgb' => [0, 0, 255]]],
            ['variant_id' => $colorVariant->id, 'name' => 'Green', 'meta' => ['hex' => '#00FF00', 'rgb' => [0, 255, 0]]],
            ['variant_id' => $colorVariant->id, 'name' => 'Silver', 'meta' => ['hex' => '#C0C0C0', 'rgb' => [192, 192, 192]]],
            ['variant_id' => $colorVariant->id, 'name' => 'Gold', 'meta' => ['hex' => '#FFD700', 'rgb' => [255, 215, 0]]],
            ['variant_id' => $colorVariant->id, 'name' => 'Space Gray', 'meta' => ['hex' => '#4A4A4A', 'rgb' => [74, 74, 74]]],
        ];

        // Create Material Options
        $materialOptions = [
            ['variant_id' => $materialVariant->id, 'name' => 'Cotton', 'meta' => ['type' => 'natural', 'breathable' => true]],
            ['variant_id' => $materialVariant->id, 'name' => 'Polyester', 'meta' => ['type' => 'synthetic', 'durable' => true]],
            ['variant_id' => $materialVariant->id, 'name' => 'Leather', 'meta' => ['type' => 'natural', 'premium' => true]],
            ['variant_id' => $materialVariant->id, 'name' => 'Mesh', 'meta' => ['type' => 'synthetic', 'breathable' => true]],
            ['variant_id' => $materialVariant->id, 'name' => 'Aluminum', 'meta' => ['type' => 'metal', 'lightweight' => true]],
            ['variant_id' => $materialVariant->id, 'name' => 'Plastic', 'meta' => ['type' => 'synthetic', 'lightweight' => true]],
        ];

        // Create Storage Options
        $storageOptions = [
            ['variant_id' => $storageVariant->id, 'name' => '64GB', 'meta' => ['capacity_gb' => 64, 'sort' => 1]],
            ['variant_id' => $storageVariant->id, 'name' => '128GB', 'meta' => ['capacity_gb' => 128, 'sort' => 2]],
            ['variant_id' => $storageVariant->id, 'name' => '256GB', 'meta' => ['capacity_gb' => 256, 'sort' => 3]],
            ['variant_id' => $storageVariant->id, 'name' => '512GB', 'meta' => ['capacity_gb' => 512, 'sort' => 4]],
            ['variant_id' => $storageVariant->id, 'name' => '1TB', 'meta' => ['capacity_gb' => 1024, 'sort' => 5]],
        ];

        // Create Style Options
        $styleOptions = [
            ['variant_id' => $styleVariant->id, 'name' => 'Casual', 'meta' => ['category' => 'everyday']],
            ['variant_id' => $styleVariant->id, 'name' => 'Formal', 'meta' => ['category' => 'business']],
            ['variant_id' => $styleVariant->id, 'name' => 'Sport', 'meta' => ['category' => 'athletic']],
            ['variant_id' => $styleVariant->id, 'name' => 'Classic', 'meta' => ['category' => 'timeless']],
        ];

        // Insert all options
        $allOptions = collect([]);
        foreach (array_merge($sizeOptions, $colorOptions, $materialOptions, $storageOptions, $styleOptions) as $option) {
            $allOptions->push(VariationOption::create($option));
        }

        return [$variants, $allOptions];
    }

    /**
     * Create stock records for all products.
     */
    private function createStocksForProducts(array $suppliers, $variantOptions): array
    {
        $products = Product::all();
        $stocks = [];

        // Group variant options by type for easier access
        $sizeOptions = $variantOptions->where('variant_id', 1)->values();
        $colorOptions = $variantOptions->where('variant_id', 2)->values();
        $storageOptions = $variantOptions->where('variant_id', 4)->values();
        $materialOptions = $variantOptions->where('variant_id', 3)->values();

        foreach ($products as $index => $product) {
            // Determine supplier based on product/brand
            $supplier = $this->selectSupplierForProduct($product, $suppliers);

            // Determine variant options based on product type
            $selectedOptions = $this->selectVariantOptionsForProduct($product, $sizeOptions, $colorOptions, $storageOptions, $materialOptions);

            // Create base stock record
            $stock = Stock::create([
                'product_id' => $product->id,
                'sku' => $this->generateSKU($product, $index),
                'barcode' => $this->generateBarcode(),
                'price' => $product->price, // Use product price
                'cost' => round((float) $product->price * 0.6, 2), // Cost is 60% of retail
                'weight' => $this->estimateWeight($product),
                'quantity_on_hand' => $product->quantity ?? rand(50, 300),
                'quantity_reserved' => 0,
                'min_stock_level' => rand(10, 30),
                'supplier_id' => $supplier->id,
                'image_url_id' => null,
                'status' => 'active',
            ]);

            // Attach variant options if applicable
            if (!empty($selectedOptions)) {
                $stock->variationOptions()->attach($selectedOptions);
            }

            $stocks[] = $stock;
        }

        return $stocks;
    }

    /**
     * Create sample reservations for testing.
     */
    private function createSampleReservations(array $stocks): array
    {
        $reservations = [];
        $stocksToReserve = array_slice($stocks, 0, 10); // Reserve from first 10 stocks

        foreach ($stocksToReserve as $index => $stock) {
            // Skip if not enough stock
            if ($stock->quantity_on_hand < 5) {
                continue;
            }

            $quantity = rand(1, min(5, $stock->quantity_on_hand));

            // Create different types of reservations
            if ($index < 6) {
                // Pending reservations (60%)
                $reservation = StockReservation::create([
                    'stock_id' => $stock->id,
                    'quantity' => $quantity,
                    'status' => 'pending',
                    'reference_type' => 'cart',
                    'reference_id' => 1000 + $index,
                    'expires_at' => now()->addMinutes(rand(5, 15)),
                    'notes' => 'Customer cart reservation',
                ]);
            } elseif ($index < 8) {
                // Committed reservations (20%)
                $reservation = StockReservation::create([
                    'stock_id' => $stock->id,
                    'quantity' => $quantity,
                    'status' => 'committed',
                    'reference_type' => 'order',
                    'reference_id' => 5000 + $index,
                    'expires_at' => now()->addDays(3),
                    'notes' => 'Order placed, payment confirmed',
                ]);
            } elseif ($index < 9) {
                // Expired reservation (10%)
                $reservation = StockReservation::create([
                    'stock_id' => $stock->id,
                    'quantity' => $quantity,
                    'status' => 'expired',
                    'reference_type' => 'cart',
                    'reference_id' => 2000 + $index,
                    'expires_at' => now()->subMinutes(10),
                    'notes' => 'Cart abandoned, reservation expired',
                ]);
            } else {
                // Fulfilled reservation (10%)
                $reservation = StockReservation::create([
                    'stock_id' => $stock->id,
                    'quantity' => $quantity,
                    'status' => 'fulfilled',
                    'reference_type' => 'order',
                    'reference_id' => 6000 + $index,
                    'expires_at' => null,
                    'notes' => 'Order completed and shipped',
                ]);
            }

            // Update stock quantity_reserved for active reservations
            if (in_array($reservation->status, ['pending', 'committed', 'checking_out'])) {
                $stock->increment('quantity_reserved', $quantity);
            }

            $reservations[] = $reservation;
        }

        return $reservations;
    }

    /**
     * Select appropriate supplier for a product.
     */
    private function selectSupplierForProduct($product, array $suppliers): Supplier
    {
        $productName = strtolower($product->name);

        // Electronics and computers
        if (
            str_contains($productName, 'phone') || str_contains($productName, 'laptop') ||
            str_contains($productName, 'computer') || str_contains($productName, 'tablet')
        ) {
            return $suppliers[array_rand([0, 1, 3, 5])]; // Tech suppliers
        }

        // Clothing and footwear
        if (
            str_contains($productName, 'shirt') || str_contains($productName, 'shoe') ||
            str_contains($productName, 'jacket')
        ) {
            return $suppliers[array_rand([2, 6])]; // Fashion suppliers
        }

        // Default to general suppliers
        return $suppliers[array_rand([4, 8])];
    }

    /**
     * Select variant options based on product type.
     */
    private function selectVariantOptionsForProduct($product, $sizeOptions, $colorOptions, $storageOptions, $materialOptions): array
    {
        $productName = strtolower($product->name);
        $options = [];

        // Electronics get color and storage
        if (str_contains($productName, 'phone') || str_contains($productName, 'laptop') || str_contains($productName, 'tablet')) {
            $options[] = $colorOptions->random()->id;
            if (str_contains($productName, 'phone') || str_contains($productName, 'laptop')) {
                $options[] = $storageOptions->random()->id;
            }
        }

        // Clothing gets size, color, and material
        if (str_contains($productName, 'shirt') || str_contains($productName, 'jacket')) {
            $options[] = $sizeOptions->random()->id;
            $options[] = $colorOptions->random()->id;
            $options[] = $materialOptions->random()->id;
        }

        // Shoes get size and color
        if (str_contains($productName, 'shoe')) {
            $options[] = $sizeOptions->random()->id;
            $options[] = $colorOptions->random()->id;
        }

        return $options;
    }

    /**
     * Generate unique SKU for product.
     */
    private function generateSKU($product, int $index): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $product->name), 0, 3));
        return sprintf('STK-%s-%04d-%s', $prefix, $index + 1, strtoupper(substr(md5($product->id . time()), 0, 4)));
    }

    /**
     * Generate random barcode (EAN-13 format).
     */
    private function generateBarcode(): ?string
    {
        return rand(0, 1) ? sprintf('%013d', rand(1000000000000, 9999999999999)) : null;
    }

    /**
     * Estimate weight based on product type.
     */
    private function estimateWeight($product): float
    {
        $productName = strtolower($product->name);

        if (str_contains($productName, 'laptop') || str_contains($productName, 'computer')) {
            return round(rand(1500, 3000) / 1000, 3); // 1.5-3 kg
        }
        if (str_contains($productName, 'phone') || str_contains($productName, 'tablet')) {
            return round(rand(150, 600) / 1000, 3); // 0.15-0.6 kg
        }
        if (str_contains($productName, 'shoe')) {
            return round(rand(300, 800) / 1000, 3); // 0.3-0.8 kg
        }
        if (str_contains($productName, 'shirt') || str_contains($productName, 'jacket')) {
            return round(rand(200, 700) / 1000, 3); // 0.2-0.7 kg
        }

        return round(rand(100, 2000) / 1000, 3); // 0.1-2 kg default
    }

    /**
     * Print seeding summary.
     */
    private function printSummary(array $suppliers, array $variants, array $stocks, array $reservations): void
    {
        $this->command->newLine();
        $this->command->info('📊 Seeding Summary:');
        $this->command->info('   • Suppliers: ' . count($suppliers));
        $this->command->info('   • Variants: ' . count($variants));
        $this->command->info('   • Stock Records: ' . count($stocks));
        $this->command->info('   • Sample Reservations: ' . count($reservations));
        $this->command->newLine();

        // Calculate statistics
        $totalQuantity = array_sum(array_map(fn($s) => $s->quantity_on_hand, $stocks));
        $totalValue = array_sum(array_map(fn($s) => $s->quantity_on_hand * $s->price, $stocks));
        $activeReservations = count(array_filter($reservations, fn($r) => in_array($r->status, ['pending', 'committed'])));

        $this->command->info('💰 Inventory Statistics:');
        $this->command->info('   • Total Units in Stock: ' . number_format($totalQuantity));
        $this->command->info('   • Total Inventory Value: $' . number_format($totalValue, 2));
        $this->command->info('   • Active Reservations: ' . $activeReservations);
        $this->command->newLine();
    }
}
