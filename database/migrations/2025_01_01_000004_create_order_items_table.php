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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            // Denormalized product fields for quick access and historical accuracy
            $table->string('product_sku', 100)->comment('Denormalized from product');
            $table->string('product_name', 255)->comment('Denormalized from product');

            // Product snapshot - CRITICAL for audit trail and handling product changes/deletions
            $table->json('product_snapshot');

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->comment('Price per unit at order time');

            // Discount fields
            $table->enum('discount_type', ['percentage', 'fixed', 'none'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable()->comment('Percentage or fixed amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Calculated discount');

            // Tax fields
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tax percentage at order time');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('Calculated tax');

            // Totals
            $table->decimal('subtotal', 10, 2)->comment('unit_price * quantity');
            $table->decimal('total_price', 10, 2)->comment('Final total after discount and tax');
            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
