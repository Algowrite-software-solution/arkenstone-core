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
        Schema::create('order_billing_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code', 20);
            $table->string('country', 2);
            $table->string('phone', 20);
            $table->string('email')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index(['country', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_billing_addresses');
    }
};
