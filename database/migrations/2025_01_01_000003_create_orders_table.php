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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('cart_id')->nullable()->constrained()->onDelete('set null');

            $table->string('order_number', 50)->unique();
            $table->enum('status', [
                'pending_payment',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'completed',
                'cancelled',
                'refunded'
            ])->default('pending_payment');

            $table->enum('payment_status', [
                'initiated',
                'processing',
                'paid',
                'failed',
                'cod_pending',
                'cod_paid',
                'refund_pending',
                'refunded',
                'partially_refunded'
            ])->default('initiated');

            // Totals
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('usd');

            // Discount
            $table->string('coupon_code', 50)->nullable();

            // Customer info
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_name');

            // Notes
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            // Tracking
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Status timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Shipping
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->timestamp('estimated_delivery')->nullable();

            // Cancellation
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_number');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
