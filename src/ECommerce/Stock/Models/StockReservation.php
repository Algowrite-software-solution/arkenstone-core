<?php

namespace Arkenstone\Core\ECommerce\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'reference_id' => 'integer',
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
        return $this->status === 'pending';
    }

    /**
     * Check if reservation is committed.
     */
    public function isCommitted(): bool
    {
        return $this->status === 'committed';
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
            $this->expires_at = now()->addMinutes($minutes);
            return $this->save();
        }
        return false;
    }

    /**
     * Commit reservation (for order placement).
     */
    public function commit(): bool
    {
        $this->status = 'committed';
        $this->expires_at = null;
        return $this->save();
    }

    /**
     * Fulfill reservation (for order shipment).
     */
    public function fulfill(): bool
    {
        if ($this->status !== 'committed') {
            return false;
        }

        $this->status = 'fulfilled';
        
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
        if (in_array($this->status, ['fulfilled', 'released'])) {
            return false;
        }

        $this->status = 'released';
        $this->stock->decrement('quantity_reserved', $this->quantity);

        return $this->save();
    }

    /**
     * Scope a query to only include active reservations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'checking_out', 'committed']);
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include committed reservations.
     */
    public function scopeCommitted(Builder $query): Builder
    {
        return $query->where('status', 'committed');
    }

    /**
     * Scope a query to only include expired reservations.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereIn('status', ['pending', 'checking_out']);
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
}
