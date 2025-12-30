<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Arkenstone\Core\ECommerce\Order\Enum\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'note',
        'changed_by',
        'changed_by_type',
        'metadata',
    ];

    protected $casts = [
        'from_status' => OrderStatus::class,
        'to_status' => OrderStatus::class,
        'metadata' => 'array',
    ];

    /**
     * Possible changed_by_type values
     */
    const CHANGED_BY_USER = 'user';
    const CHANGED_BY_ADMIN = 'admin';
    const CHANGED_BY_SYSTEM = 'system';
    const CHANGED_BY_WEBHOOK = 'webhook';

    /**
     * Get the order that owns the history
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope: Get history by order
     */
    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope: Get history by status
     */
    public function scopeToStatus($query, OrderStatus|string $status)
    {
        if (is_string($status)) {
            $status = OrderStatus::from($status);
        }
        return $query->where('to_status', $status);
    }

    /**
     * Scope: Get history by changed_by_type
     */
    public function scopeByChangedByType($query, string $type)
    {
        return $query->where('changed_by_type', $type);
    }

    /**
     * Scope: Get system-initiated changes
     */
    public function scopeSystemChanges($query)
    {
        return $query->where('changed_by_type', self::CHANGED_BY_SYSTEM);
    }

    /**
     * Scope: Get user-initiated changes
     */
    public function scopeUserChanges($query)
    {
        return $query->where('changed_by_type', self::CHANGED_BY_USER);
    }

    /**
     * Scope: Get admin-initiated changes
     */
    public function scopeAdminChanges($query)
    {
        return $query->where('changed_by_type', self::CHANGED_BY_ADMIN);
    }

    /**
     * Get formatted status transition
     */
    public function getFormattedTransitionAttribute(): string
    {
        $from = $this->from_status ? $this->from_status->label() : 'None';
        $to = $this->to_status->label();

        return "{$from} → {$to}";
    }

    /**
     * Get human-readable changed by text
     */
    public function getChangedByTextAttribute(): string
    {
        return match ($this->changed_by_type) {
            self::CHANGED_BY_USER => "Customer",
            self::CHANGED_BY_ADMIN => "Admin #{$this->changed_by}",
            self::CHANGED_BY_SYSTEM => "System",
            self::CHANGED_BY_WEBHOOK => "Payment Gateway",
            default => "Unknown",
        };
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('M d, Y g:i A');
    }
}
