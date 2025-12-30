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
        Schema::create('stock_variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->foreignId('variant_option_id')->constrained('variation_options')->onDelete('cascade');

            $table->unique(['stock_id', 'variant_option_id'], 'stock_variant_unique');
            $table->index('stock_id');
            $table->index('variant_option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_variant_options');
    }
};
