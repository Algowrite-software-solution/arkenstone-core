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
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('processed_by')->nullable()->comment('Admin user ID who processed refund');

            $table->string('refund_number', 50)->unique();
            $table->decimal('refund_amount', 10, 2)->comment('Amount being refunded');
            $table->string('currency', 3)->default('usd');
            $table->string('refund_method', 50)->nullable()->comment('original, bank_transfer, etc.');
            $table->text('reason')->nullable()->comment('Customer-facing reason');
            $table->enum('status', ['pending', 'pending_manual', 'processing', 'completed', 'failed'])->default('pending');

            // Gateway integration
            $table->string('gateway_refund_id')->nullable();
            $table->json('gateway_response')->nullable();

            // Who initiated the refund
            $table->unsignedBigInteger('initiated_by_id')->nullable();
            $table->string('initiated_by_type', 20)->default('admin'); // admin, system, user

            // Notes
            $table->text('admin_note')->nullable()->comment('Internal admin note');

            // Timestamps
            $table->timestamp('initiated_at')->nullable()->comment('When refund was initiated');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->date('estimated_completion')->nullable()->comment('For manual refunds');
            $table->timestamps();

            // Indexes
            $table->index(['payment_id', 'status']);
            $table->index('refund_number');
            $table->index('gateway_refund_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
