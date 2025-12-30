<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            // Denormalized product fields for quick access
            $table->string('product_sku', 100)->nullable()->comment('Denormalized from product');
            $table->string('product_name', 255)->nullable()->comment('Denormalized from product');

            // Product snapshot - preserves product data at time of adding to cart
            $table->json('product_snapshot')->nullable();

            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->comment('Price per unit');

            // Discount fields
            $table->enum('discount_type', ['percentage', 'fixed', 'none'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable()->comment('Percentage or fixed amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Calculated discount');

            // Tax fields
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tax percentage');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('Calculated tax');

            // Totals
            $table->decimal('subtotal', 10, 2)->comment('unit_price * quantity');
            $table->decimal('total_price', 10, 2)->comment('Final total after discount and tax');
            // Indexes
            $table->index(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
