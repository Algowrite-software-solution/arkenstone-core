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

            $table->string('refund_number', 50)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

            // Gateway integration
            $table->string('gateway_refund_id')->nullable();
            $table->json('gateway_response')->nullable();

            // Who initiated the refund
            $table->unsignedBigInteger('initiated_by_id')->nullable();
            $table->string('initiated_by_type', 20)->default('admin'); // admin, system, user

            // Timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failed_reason')->nullable();

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
