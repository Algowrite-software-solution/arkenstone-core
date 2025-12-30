<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('checkout_token', 64)->unique();
            
            // Session data stores calculated values and user selections
            $table->json('session_data')->comment('Contains: shipping_address, billing_address, shipping_method, payment_method, totals, etc.');
            
            $table->timestamp('expires_at')->comment('Sessions expire after 15 minutes');
            $table->timestamps();
            
            // Indexes
            $table->index('checkout_token');
            $table->index(['cart_id', 'expires_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_sessions');
    }
};
