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
        Schema::create('product_taxonomies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('taxonomy_id')->constrained('taxonomies')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'taxonomy_id']);
            $table->index('product_id');
            $table->index('taxonomy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_taxonomies');
    }
};
