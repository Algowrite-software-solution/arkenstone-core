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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');

            $table->enum('transaction_type', [
                'charge',
                'refund',
                'partial_refund',
                'authorization',
                'capture',
                'void'
            ])->comment('Type of transaction event');

            $table->string('transaction_id')->comment('Gateway transaction ID');
            $table->decimal('amount', 10, 2);
            $table->string('status', 50)->comment('Gateway-specific status');
            $table->json('gateway_response')->nullable()->comment('Full response from payment gateway');

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['payment_id', 'transaction_type']);
            $table->index('transaction_id');
            $table->index(['payment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
