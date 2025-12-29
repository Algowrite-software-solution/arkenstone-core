<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Arkenstone\Core\ECommerce\Order\Enum\OrderStatus;
use Arkenstone\Core\ECommerce\Order\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'cart_id',
        'order_number',
        'status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'tax_amount',
        'total',
        'currency',
        'coupon_code',
        'customer_email',
        'customer_phone',
        'customer_name',
        'notes',
        'admin_notes',
        'ip_address',
        'user_agent',
        'confirmed_at',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'tracking_number',
        'carrier',
        'estimated_delivery',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'processing_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'estimated_delivery' => 'datetime',
    ];

    /**
     * Get the user that owns the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    /**
     * Get the cart associated with this order
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the shipping address
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderShippingAddress::class);
    }

    /**
     * Get the billing address
     */
    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderBillingAddress::class);
    }

    /**
     * Get the payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the primary payment
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Get the status history
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Scope: Get orders by status
     */
    public function scopeWithStatus($query, OrderStatus|string $status)
    {
        if (is_string($status)) {
            $status = OrderStatus::from($status);
        }
        return $query->where('status', $status);
    }

    /**
     * Scope: Get orders by payment status
     */
    public function scopeWithPaymentStatus($query, PaymentStatus|string $status)
    {
        if (is_string($status)) {
            $status = PaymentStatus::from($status);
        }
        return $query->where('payment_status', $status);
    }

    /**
     * Scope: Get confirmed orders
     */
    public function scopeConfirmed($query)
    {
        return $query->whereIn('status', [
            OrderStatus::CONFIRMED,
            OrderStatus::PROCESSING,
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED,
            OrderStatus::COMPLETED,
        ]);
    }

    /**
     * Scope: Get pending payment orders
     */
    public function scopePendingPayment($query)
    {
        return $query->where('status', OrderStatus::PENDING_PAYMENT);
    }

    /**
     * Scope: Get cancelled orders
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatus::CANCELLED);
    }

    /**
     * Scope: Get orders by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get orders by order number
     */
    public function scopeByOrderNumber($query, string $orderNumber)
    {
        return $query->where('order_number', $orderNumber);
    }

    /**
     * Scope: Get orders created in date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Get orders pending for specific time
     */
    public function scopePendingForMinutes($query, int $minutes)
    {
        return $query->where('status', OrderStatus::PENDING_PAYMENT)
            ->where('created_at', '<=', now()->subMinutes($minutes));
    }

    /**
     * Scope: Get delivered orders not yet completed
     */
    public function scopeDeliveredNotCompleted($query, int $days = 7)
    {
        return $query->where('status', OrderStatus::DELIVERED)
            ->where('delivered_at', '<=', now()->subDays($days));
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    /**
     * Update order status
     */
    public function updateStatus(OrderStatus $newStatus, ?string $reason = null, ?int $changedById = null, string $changedByType = 'user'): void
    {
        $oldStatus = $this->status;

        // Validate transition
        if (!$oldStatus->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException("Cannot transition from {$oldStatus->value} to {$newStatus->value}");
        }

        $this->status = $newStatus;

        // Update timestamp fields based on status
        match ($newStatus) {
            OrderStatus::CONFIRMED => $this->confirmed_at = now(),
            OrderStatus::PROCESSING => $this->processing_at = now(),
            OrderStatus::SHIPPED => $this->shipped_at = now(),
            OrderStatus::DELIVERED => $this->delivered_at = now(),
            OrderStatus::COMPLETED => $this->completed_at = now(),
            OrderStatus::CANCELLED => $this->cancelled_at = now(),
            default => null,
        };

        if ($newStatus === OrderStatus::CANCELLED && $reason) {
            $this->cancellation_reason = $reason;
        }

        $this->save();

        // Create status history record
        $this->statusHistory()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'reason' => $reason,
            'changed_by_id' => $changedById,
            'changed_by_type' => $changedByType,
        ]);
    }

    /**
     * Mark order as confirmed
     */
    public function markConfirmed(): void
    {
        $this->updateStatus(OrderStatus::CONFIRMED, 'Payment confirmed', null, 'system');
    }

    /**
     * Mark order as processing
     */
    public function markProcessing(): void
    {
        $this->updateStatus(OrderStatus::PROCESSING);
    }

    /**
     * Mark order as shipped
     */
    public function markShipped(string $trackingNumber, string $carrier): void
    {
        $this->tracking_number = $trackingNumber;
        $this->carrier = $carrier;
        $this->updateStatus(OrderStatus::SHIPPED);
    }

    /**
     * Mark order as delivered
     */
    public function markDelivered(): void
    {
        $this->updateStatus(OrderStatus::DELIVERED);
    }

    /**
     * Mark order as completed
     */
    public function markCompleted(): void
    {
        $this->updateStatus(OrderStatus::COMPLETED);
    }

    /**
     * Cancel order
     */
    public function cancel(string $reason, ?int $cancelledBy = null, string $cancelledByType = 'user'): void
    {
        if (!$this->status->isCancellable()) {
            throw new \InvalidArgumentException("Order in status {$this->status->value} cannot be cancelled");
        }

        $this->updateStatus(OrderStatus::CANCELLED, $reason, $cancelledBy, $cancelledByType);
    }

    /**
     * Check if order can be cancelled
     */
    public function isCancellable(): bool
    {
        return $this->status->isCancellable();
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status->isSuccessful();
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get formatted order number for display
     */
    public function getFormattedOrderNumberAttribute(): string
    {
        return $this->order_number;
    }
}
