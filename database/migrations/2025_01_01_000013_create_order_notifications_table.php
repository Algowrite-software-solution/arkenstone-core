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
        Schema::create('order_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('notification_type', [
                'order_confirmation',
                'payment_confirmation',
                'order_shipped',
                'order_delivered',
                'order_cancelled',
                'payment_reminder',
                'payment_failed',
                'refund_initiated',
                'refund_completed'
            ])->comment('Type of notification sent');

            $table->enum('channel', ['email', 'sms', 'push'])->comment('Notification channel');

            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();

            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->text('failed_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'notification_type']);
            $table->index(['status', 'created_at']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_notifications');
    }
};
