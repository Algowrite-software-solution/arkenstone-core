<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Arkenstone\Core\ECommerce\Order\Enum\CartStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'cart_token',
        'session_id',
        'status',
        'order_id',
        'migrated_to_cart_id',
        'coupon_code',
        'discount_type',
        'discount_value',
        'discount_amount',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'total',
        'notes',
        'expires_at',
        'completed_at',
        'migrated_at',
    ];

    protected $casts = [
        'status' => CartStatus::class,
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'migrated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    /**
     * Get the cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order associated with this cart
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the cart this was migrated to (for guest to logged-in user migration)
     */
    public function migratedToCart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'migrated_to_cart_id');
    }

    /**
     * Get carts that were migrated into this cart
     */
    public function migratedFromCarts(): HasMany
    {
        return $this->hasMany(Cart::class, 'migrated_to_cart_id');
    }

    /**
     * Scope: Get active carts
     */
    public function scopeActive($query)
    {
        return $query->where('status', CartStatus::ACTIVE);
    }

    /**
     * Scope: Get guest carts (no user_id)
     */
    public function scopeGuest($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope: Get carts by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get carts by token
     */
    public function scopeByToken($query, string $token)
    {
        return $query->where('cart_token', $token);
    }

    /**
     * Scope: Get carts by session
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope: Get expired carts
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
            ->where('status', CartStatus::ACTIVE);
    }

    /**
     * Calculate cart totals
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total');

        // Calculate discount
        if ($this->discount_value && $this->discount_type) {
            $discountType = \Arkenstone\Core\ECommerce\Order\Enum\DiscountType::from($this->discount_type);
            $this->discount_amount = $discountType->calculate($this->subtotal, $this->discount_value);
        } else {
            $this->discount_amount = 0;
        }

        // Calculate total
        $this->total = $this->subtotal - $this->discount_amount + $this->shipping_cost + $this->tax_amount;
    }

    /**
     * Mark cart as completed
     */
    public function markCompleted(Order $order): void
    {
        $this->status = CartStatus::COMPLETED;
        $this->order_id = $order->id;
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Mark cart as migrated
     */
    public function markMigrated(Cart $targetCart): void
    {
        $this->status = CartStatus::MIGRATED;
        $this->migrated_to_cart_id = $targetCart->id;
        $this->migrated_at = now();
        $this->save();
    }

    /**
     * Check if cart is modifiable
     */
    public function isModifiable(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Check if cart is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Generate unique cart token
     */
    public static function generateToken(): string
    {
        return 'cart_' . bin2hex(random_bytes(16));
    }

    /**
     * Get cart item count
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}
