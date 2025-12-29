<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Arkenstone\Core\ECommerce\Order\Enum\PaymentMethod;
use Arkenstone\Core\ECommerce\Order\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'status',
        'amount',
        'currency',
        'gateway',
        'gateway_payment_id',
        'gateway_response',
        'redirect_url',
        'callback_url',
        'failed_reason',
        'paid_at',
        'failed_at',
        'refunded_amount',
        'refund_status',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the order that owns the payment
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the refunds for this payment
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    /**
     * Scope: Get payments by status
     */
    public function scopeWithStatus($query, PaymentStatus|string $status)
    {
        if (is_string($status)) {
            $status = PaymentStatus::from($status);
        }
        return $query->where('status', $status);
    }

    /**
     * Scope: Get successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [PaymentStatus::PAID, PaymentStatus::COD_PENDING]);
    }

    /**
     * Scope: Get failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    /**
     * Scope: Get payments by method
     */
    public function scopeByMethod($query, PaymentMethod|string $method)
    {
        if (is_string($method)) {
            $method = PaymentMethod::from($method);
        }
        return $query->where('payment_method', $method);
    }

    /**
     * Scope: Get payments by gateway
     */
    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope: Get COD payments
     */
    public function scopeCOD($query)
    {
        return $query->where('payment_method', PaymentMethod::COD);
    }

    /**
     * Scope: Get refundable payments
     */
    public function scopeRefundable($query)
    {
        return $query->whereIn('status', [
            PaymentStatus::PAID,
            PaymentStatus::COD_PENDING,
            PaymentStatus::REFUNDING,
        ]);
    }

    /**
     * Mark payment as paid
     */
    public function markPaid(?array $gatewayResponse = null): void
    {
        $this->status = PaymentStatus::PAID;

        $this->paid_at = now();

        if ($gatewayResponse) {
            $this->gateway_response = array_merge(
                $this->gateway_response ?? [],
                $gatewayResponse
            );
        }

        $this->save();
    }

    /**
     * Mark payment as failed
     */
    public function markFailed(string $reason, ?array $gatewayResponse = null): void
    {
        $this->status = PaymentStatus::FAILED;
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
     * Update refund status
     */
    public function updateRefundStatus(): void
    {
        $totalRefunded = $this->refunds()
            ->where('status', 'completed')
            ->sum('amount');

        $this->refunded_amount = $totalRefunded;

        if ($totalRefunded >= $this->amount) {
            $this->status = PaymentStatus::REFUNDED;
            $this->refund_status = 'full';
        } elseif ($totalRefunded > 0) {
            $this->status = PaymentStatus::REFUNDING;
            $this->refund_status = 'partial';
        }

        $this->save();
    }

    /**
     * Get remaining refundable amount
     */
    public function getRemainingRefundableAmountAttribute(): float
    {
        return max(0, $this->amount - $this->refunded_amount);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        $refundableStatuses = [PaymentStatus::PAID, PaymentStatus::COD_PENDING, PaymentStatus::REFUNDING];
        return in_array($this->status, $refundableStatuses) && $this->remaining_refundable_amount > 0;
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    /**
     * Check if payment requires gateway
     */
    public function requiresGateway(): bool
    {
        return $this->payment_method->requiresGateway();
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }
}
