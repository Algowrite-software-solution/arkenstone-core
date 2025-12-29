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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('cart_token', 100)->unique();
            $table->string('session_id', 255)->nullable()->index();
            $table->enum('status', ['active', 'abandoned', 'completed', 'migrated', 'expired'])->default('active');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('migrated_to_cart_id')->nullable()->constrained('carts')->onDelete('set null');

            // Discount fields
            $table->string('coupon_code', 50)->nullable();
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);

            // Totals
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('migrated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('cart_token');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
