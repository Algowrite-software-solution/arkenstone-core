<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'full_name',
        'phone',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the order that owns the address
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get formatted address (single line)
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted address (multi-line)
     */
    public function getFormattedAddressMultilineAttribute(): string
    {
        $lines = [];

        $lines[] = $this->full_name;
        $lines[] = $this->address_line_1;

        if ($this->address_line_2) {
            $lines[] = $this->address_line_2;
        }

        $lines[] = "{$this->city}, {$this->state} {$this->postal_code}";
        $lines[] = $this->country;

        if ($this->phone) {
            $lines[] = "Phone: {$this->phone}";
        }

        return implode("\n", $lines);
    }

    /**
     * Scope: Get addresses by country
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope: Get addresses by state
     */
    public function scopeByState($query, string $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope: Get addresses by city
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }
}
