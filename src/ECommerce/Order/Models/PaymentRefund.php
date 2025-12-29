<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'refund_number',
        'amount',
        'currency',
        'reason',
        'status',
        'gateway_refund_id',
        'gateway_response',
        'initiated_by_id',
        'initiated_by_type',
        'completed_at',
        'failed_at',
        'failed_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Possible refund statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the payment that owns the refund
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Scope: Get refunds by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get completed refunds
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Get pending refunds
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Get failed refunds
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Get refunds by payment
     */
    public function scopeForPayment($query, int $paymentId)
    {
        return $query->where('payment_id', $paymentId);
    }

    /**
     * Generate unique refund number
     */
    public static function generateRefundNumber(): string
    {
        return 'REF-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    /**
     * Mark refund as completed
     */
    public function markCompleted(?array $gatewayResponse = null): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();

        if ($gatewayResponse) {
            $this->gateway_response = array_merge(
                $this->gateway_response ?? [],
                $gatewayResponse
            );
        }

        $this->save();

        // Update payment refund status
        $this->payment->updateRefundStatus();
    }

    /**
     * Mark refund as failed
     */
    public function markFailed(string $reason, ?array $gatewayResponse = null): void
    {
        $this->status = self::STATUS_FAILED;
        $this->failed_at = now();
        $this->failed_reason = $reason;

        if ($gatewayResponse) {
            $this->gateway_response = array_merge(
                $this->gateway_response ?? [],
                $gatewayResponse
            );
        }

        $this->save();
    }

    /**
     * Mark refund as processing
     */
    public function markProcessing(?array $gatewayResponse = null): void
    {
        $this->status = self::STATUS_PROCESSING;

        if ($gatewayResponse) {
            $this->gateway_response = array_merge(
                $this->gateway_response ?? [],
                $gatewayResponse
            );
        }

        $this->save();
    }

    /**
     * Check if refund is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if refund is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }
}
