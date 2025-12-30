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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->enum('from_status', [
                'pending_payment',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'completed',
                'cancelled',
                'refunded'
            ])->nullable();

            $table->enum('to_status', [
                'pending_payment',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'completed',
                'cancelled',
                'refunded'
            ]);

            $table->text('note')->nullable()->comment('Human-readable note about the change');

            // Who made the change
            $table->unsignedBigInteger('changed_by')->nullable()->comment('User ID who made the change');
            $table->enum('changed_by_type', ['system', 'customer', 'admin'])->default('system');

            // Additional metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'created_at']);
            $table->index('to_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};