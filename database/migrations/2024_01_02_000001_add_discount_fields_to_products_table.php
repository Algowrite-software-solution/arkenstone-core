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
        Schema::table('products', function (Blueprint $table) {
            // Add discount fields
            $table->enum('discount_type', ['percentage', 'fixed_amount'])->nullable()->after('price');
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');

            // Rename stock_quantity to quantity
            $table->renameColumn('stock_quantity', 'quantity');

            // Remove sale_price (replaced by discount system)
            $table->dropColumn('sale_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
            $table->renameColumn('quantity', 'stock_quantity');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
        });
    }
};
