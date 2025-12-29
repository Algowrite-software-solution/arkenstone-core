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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->enum('payment_method', ['card', 'bank_transfer', 'upi', 'cod', 'wallet']);
            $table->enum('status', [
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

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');

            // Gateway integration
            $table->string('gateway', 50)->nullable(); // stripe, paypal, razorpay
            $table->string('gateway_payment_id')->nullable();
            $table->json('gateway_response')->nullable();

            // URLs for payment flow
            $table->text('redirect_url')->nullable();
            $table->text('callback_url')->nullable();

            // Failure handling
            $table->text('failed_reason')->nullable();

            // Refund tracking
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->string('refund_status', 20)->nullable(); // full, partial

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('gateway_payment_id');
            $table->index(['payment_method', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
