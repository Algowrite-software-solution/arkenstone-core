<?php

namespace Arkenstone\Core\ECommerce\Stock\Models;

use Arkenstone\Core\Database\Factories\StockReservationFactory;
use Arkenstone\Core\ECommerce\Stock\Enum\StockReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StockReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'quantity',
        'status',
        'reference_type',
        'reference_id',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'stock_id' => 'integer',
        'quantity' => 'integer',
        'reference_id' => 'string',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the stock that owns the reservation.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Check if reservation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if reservation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === StockReservationStatus::PENDING->value;
    }

    /**
     * Check if reservation is committed.
     */
    public function isCommitted(): bool
    {
        return $this->status === StockReservationStatus::COMMITTED->value;
    }

    /**
     * Update reservation status.
     */
    public function updateStatus(string $status): bool
    {
        $this->status = $status;
        return $this->save();
    }

    /**
     * Extend reservation expiry.
     */
    public function extend(int $minutes): bool
    {
        if ($this->expires_at) {
            $this->expires_at = $this->expires_at->addMinutes($minutes);
            return $this->save();
        }
        return false;
    }

    /**
     * Commit reservation (for order placement).
     */
    public function commit(): bool
    {
        $this->status = StockReservationStatus::COMMITTED->value;
        $this->expires_at = null;
        return $this->save();
    }

    /**
     * Fulfill reservation (for order shipment).
     */
    public function fulfill(): bool
    {
        if ($this->status !== StockReservationStatus::COMMITTED->value) {
            return false;
        }

        $this->status = StockReservationStatus::FULFILLED->value;

        // Deduct from stock
        $this->stock->decrement('quantity_on_hand', $this->quantity);
        $this->stock->decrement('quantity_reserved', $this->quantity);

        return $this->save();
    }

    /**
     * Release reservation.
     */
    public function release(): bool
    {
        if (in_array($this->status, [StockReservationStatus::FULFILLED->value, StockReservationStatus::RELEASED->value])) {
            return false;
        }

        $this->status = StockReservationStatus::RELEASED->value;
        $this->stock->decrement('quantity_reserved', $this->quantity);

        return $this->save();
    }

    /**
     * Scope a query to only include active reservations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', StockReservationStatus::activeStatuses());
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', StockReservationStatus::PENDING->value);
    }

    /**
     * Scope a query to only include committed reservations.
     */
    public function scopeCommitted(Builder $query): Builder
    {
        return $query->where('status', StockReservationStatus::COMMITTED->value);
    }

    /**
     * Scope a query to only include expired reservations.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereIn('status', StockReservationStatus::expiredStatuses());
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by reference.
     */
    public function scopeByReference(Builder $query, string $type, int $id): Builder
    {
        return $query->where('reference_type', $type)
            ->where('reference_id', $id);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return StockReservationFactory::new();
    }
}
