# Order & Stock Module Implementation Guide

**Version:** 1.0.0  
**Target Modules:** Order Management & Stock/Inventory Management  
**Priority:** Phase 1 & 2 (Weeks 1-7)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Order Module Implementation](#order-module-implementation)
3. [Stock Module Implementation](#stock-module-implementation)
4. [Integration Between Modules](#integration-between-modules)
5. [Testing Strategy](#testing-strategy)
6. [Code Examples](#code-examples)

---

## 📖 Overview

This guide provides detailed implementation instructions for the Order and Stock modules, following the existing Arkenstone Core architecture patterns established by the Product module.

### Architecture Principles

1. **Follow existing patterns** from `src/ECommerce/Product/`
2. **Use service-based architecture** with dedicated ServiceProviders
3. **Implement contract-first design** using interfaces
4. **Leverage ResponseProtocol** for all API responses
5. **Use event-driven architecture** for module communication
6. **Write comprehensive tests** using Orchestra Testbench

---

## 📦 Order Module Implementation

### Directory Structure

```
src/ECommerce/Order/
├── Http/
│   ├── Controllers/
│   │   └── API/
│   │       └── V1/
│   │           ├── OrderController.php
│   │           ├── OrderItemController.php
│   │           └── OrderStatusController.php
│   ├── Requests/
│   │   ├── StoreOrderRequest.php
│   │   ├── UpdateOrderRequest.php
│   │   ├── UpdateOrderStatusRequest.php
│   │   ├── AddOrderItemRequest.php
│   │   └── CancelOrderRequest.php
│   └── Resources/
│       ├── OrderResource.php
│       ├── OrderItemResource.php
│       ├── OrderStatusHistoryResource.php
│       └── Collection/
│           └── OrderCollection.php
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   ├── OrderStatusHistory.php
│   └── Address.php
├── Services/
│   ├── OrderService.php
│   ├── OrderItemService.php
│   └── OrderStatusService.php
├── Provider/
│   └── OrderServiceProvider.php
├── Contracts/
│   └── OrderServiceContract.php
└── routes/
    └── api.php
```

### Step 1: Create Order Model

**File:** `src/ECommerce/Order/Models/Order.php`

```php
<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Arkenstone\Core\ECommerce\Product\Models\Product;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_email',
        'customer_name',
        'customer_phone',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'notes',
        'billing_address_id',
        'shipping_address_id',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $dates = [
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'deleted_at',
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors & Mutators
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    // Methods
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['shipped', 'delivered', 'completed', 'cancelled']);
    }

    public function canBeRefunded(): bool
    {
        return $this->payment_status === 'paid' && 
               in_array($this->status, ['processing', 'shipped', 'delivered', 'completed']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            
            if (empty($order->currency)) {
                $order->currency = config('arkenstone.order.default_currency', 'USD');
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = config('arkenstone.order.number_prefix', 'ORD-');
        $length = config('arkenstone.order.number_length', 8);
        
        do {
            $number = $prefix . strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
        } while (static::where('order_number', $number)->exists());
        
        return $number;
    }
}
```

### Step 2: Create Order Service

**File:** `src/ECommerce/Order/Services/OrderService.php`

```php
<?php

namespace Arkenstone\Core\ECommerce\Order\Services;

use Arkenstone\Core\ECommerce\Order\Models\Order;
use Arkenstone\Core\ECommerce\Order\Models\OrderStatusHistory;
use Arkenstone\Core\ECommerce\Order\Contracts\OrderServiceContract;
use Arkenstone\Core\ECommerce\Contracts\Service;
use Arkenstone\Core\Support\Event;
use Illuminate\Support\Facades\DB;

class OrderService implements Service, OrderServiceContract
{
    public function getName(): string
    {
        return 'Order Service';
    }

    public function createOrder(array $data): Order
    {
        Event::dispatch('order.creating', [$data]);

        $order = DB::transaction(function () use ($data) {
            // Calculate totals
            $totals = $this->calculateOrderTotals(
                $data['items'] ?? [],
                $data['discount_amount'] ?? 0,
                $data['shipping_amount'] ?? 0,
                $data['tax_rate'] ?? 0
            );

            // Create order
            $order = Order::create([
                'customer_id' => $data['customer_id'] ?? null,
                'customer_email' => $data['customer_email'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? null,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'discount_amount' => $totals['discount_amount'],
                'total_amount' => $totals['total_amount'],
                'notes' => $data['notes'] ?? null,
                'billing_address_id' => $data['billing_address_id'] ?? null,
                'shipping_address_id' => $data['shipping_address_id'] ?? null,
            ]);

            // Add order items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create($item);
                }
            }

            // Record initial status
            $this->recordStatusChange($order, 'pending', 'Order created');

            return $order;
        });

        Event::dispatch('order.created', [$order]);

        return $order->load(['items', 'billingAddress', 'shippingAddress']);
    }

    public function updateOrder(Order $order, array $data): Order
    {
        Event::dispatch('order.updating', [$order, $data]);

        $order->update($data);

        Event::dispatch('order.updated', [$order]);

        return $order->fresh(['items', 'billingAddress', 'shippingAddress']);
    }

    public function cancelOrder(Order $order, string $reason = ''): Order
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled in its current status.');
        }

        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $this->recordStatusChange($order, 'cancelled', $reason ?: 'Order cancelled');
        });

        Event::dispatch('order.cancelled', [$order, $reason]);
        Event::dispatch('order.status.changed', [$order, $oldStatus, 'cancelled']);

        return $order->fresh();
    }

    public function updateOrderStatus(Order $order, string $status, string $comment = ''): Order
    {
        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $status, $comment) {
            $order->update(['status' => $status]);

            // Update timestamps based on status
            if ($status === 'shipped' && !$order->shipped_at) {
                $order->update(['shipped_at' => now()]);
            } elseif ($status === 'delivered' && !$order->delivered_at) {
                $order->update(['delivered_at' => now()]);
            }

            $this->recordStatusChange($order, $status, $comment ?: "Status changed to {$status}");
        });

        Event::dispatch('order.status.changed', [$order, $oldStatus, $status]);

        if ($status === 'completed') {
            Event::dispatch('order.completed', [$order]);
        }

        return $order->fresh();
    }

    public function calculateOrderTotals(array $items, float $discountAmount = 0, float $shippingAmount = 0, float $taxRate = 0): array
    {
        $subtotal = collect($items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping_amount' => round($shippingAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round($total, 2),
        ];
    }

    protected function recordStatusChange(Order $order, string $status, string $comment): void
    {
        $order->statusHistory()->create([
            'status' => $status,
            'comment' => $comment,
            'notified' => false,
        ]);
    }

    public function getOrders(array $filters = [])
    {
        $query = Order::query()->with(['items', 'billingAddress', 'shippingAddress']);

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->byCustomer($filters['customer_id']);
        }

        if (!empty($filters['payment_status'])) {
            $query->byPaymentStatus($filters['payment_status']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        if (!empty($filters['order_number'])) {
            $query->where('order_number', $filters['order_number']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->latest()->paginate($perPage);
    }
}
```

### Step 3: Create Order Controller

**File:** `src/ECommerce/Order/Http/Controllers/API/V1/OrderController.php`

```php
<?php

namespace Arkenstone\Core\ECommerce\Order\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Arkenstone\Core\ECommerce\Order\Services\OrderService;
use Arkenstone\Core\ECommerce\Order\Http\Requests\StoreOrderRequest;
use Arkenstone\Core\ECommerce\Order\Http\Requests\UpdateOrderRequest;
use Arkenstone\Core\ECommerce\Order\Http\Requests\UpdateOrderStatusRequest;
use Arkenstone\Core\ECommerce\Order\Http\Resources\OrderResource;
use Arkenstone\Core\ECommerce\Order\Http\Resources\Collection\OrderCollection;
use Arkenstone\Core\ECommerce\Order\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status', 
            'customer_id', 
            'payment_status', 
            'order_number',
            'date_from',
            'date_to',
            'per_page'
        ]);

        $orders = $this->orderService->getOrders($filters);

        return ResponseProtocol::success(
            new OrderCollection($orders),
            'Orders retrieved successfully'
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());

            return ResponseProtocol::success(
                new OrderResource($order),
                'Order created successfully',
                201
            );
        } catch (\Exception $e) {
            return ResponseProtocol::error(
                ['error' => $e->getMessage()],
                'Failed to create order',
                500
            );
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.product', 'billingAddress', 'shippingAddress', 'statusHistory']);

        return ResponseProtocol::success(
            new OrderResource($order),
            'Order retrieved successfully'
        );
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->updateOrder($order, $request->validated());

            return ResponseProtocol::success(
                new OrderResource($updatedOrder),
                'Order updated successfully'
            );
        } catch (\Exception $e) {
            return ResponseProtocol::error(
                ['error' => $e->getMessage()],
                'Failed to update order',
                500
            );
        }
    }

    public function destroy(Order $order): JsonResponse
    {
        try {
            $this->orderService->cancelOrder($order, 'Order cancelled by admin');

            return ResponseProtocol::success(
                null,
                'Order cancelled successfully'
            );
        } catch (\Exception $e) {
            return ResponseProtocol::error(
                ['error' => $e->getMessage()],
                'Failed to cancel order',
                500
            );
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->updateOrderStatus(
                $order,
                $request->input('status'),
                $request->input('comment', '')
            );

            return ResponseProtocol::success(
                new OrderResource($updatedOrder),
                'Order status updated successfully'
            );
        } catch (\Exception $e) {
            return ResponseProtocol::error(
                ['error' => $e->getMessage()],
                'Failed to update order status',
                500
            );
        }
    }
}
```

### Step 4: Create Order Service Provider

**File:** `src/ECommerce/Order/Provider/OrderServiceProvider.php`

```php
<?php

namespace Arkenstone\Core\ECommerce\Order\Provider;

use Illuminate\Support\ServiceProvider;
use Arkenstone\Core\ECommerce\Order\Services\OrderService;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('order', function () {
            return new OrderService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
```

### Step 5: Register Order Provider

**File:** `src/CoreServiceProvider.php` (Update)

```php
public function register(): void
{
    // ... existing code ...
    
    // Register Order Service Provider
    $this->app->register(\Arkenstone\Core\ECommerce\Order\Provider\OrderServiceProvider::class);
}
```

---

## 📊 Stock Module Implementation

### Directory Structure

```
src/ECommerce/Stock/
├── Http/
│   ├── Controllers/
│   │   └── API/
│   │       └── V1/
│   │           ├── StockController.php
│   │           ├── WarehouseController.php
│   │           └── StockMovementController.php
│   ├── Requests/
│   │   ├── AdjustStockRequest.php
│   │   ├── ReserveStockRequest.php
│   │   ├── TransferStockRequest.php
│   │   └── StoreWarehouseRequest.php
│   └── Resources/
│       ├── StockItemResource.php
│       ├── StockMovementResource.php
│       └── WarehouseResource.php
├── Models/
│   ├── StockItem.php
│   ├── StockMovement.php
│   ├── StockReservation.php
│   └── Warehouse.php
├── Services/
│   ├── StockService.php
│   └── StockMovementService.php
├── Provider/
│   └── StockServiceProvider.php
├── Contracts/
│   └── StockServiceContract.php
└── routes/
    └── api.php
```

### Step 1: Create StockItem Model

**File:** `src/ECommerce/Stock/Models/StockItem.php`

```php
<?php

namespace Arkenstone\Core\ECommerce\Stock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Arkenstone\Core\ECommerce\Product\Models\Product;

class StockItem extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'reorder_point',
        'reorder_quantity',
        'is_tracked',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'is_tracked' => 'boolean',
    ];

    protected $appends = ['available_quantity'];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    // Accessors
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_quantity <= $this->reorder_point;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->available_quantity <= 0;
    }

    // Scopes
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= 0');
    }

    public function scopeTracked($query)
    {
        return $query->where('is_tracked', true);
    }
}
```

### Step 2: Create Stock Service

**File:** `src/ECommerce/Stock/Services/StockService.php`

```php
<?php

namespace Arkenstone\Core\ECommerce\Stock\Services;

use Arkenstone\Core\ECommerce\Stock\Models\StockItem;
use Arkenstone\Core\ECommerce\Stock\Models\StockReservation;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Stock\Contracts\StockServiceContract;
use Arkenstone\Core\ECommerce\Contracts\Service;
use Arkenstone\Core\Support\Event;
use Illuminate\Support\Facades\DB;

class StockService implements Service, StockServiceContract
{
    public function getName(): string
    {
        return 'Stock Service';
    }

    public function getAvailableStock(Product $product, ?int $warehouseId = null): int
    {
        $query = StockItem::where('product_id', $product->id);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->sum(DB::raw('quantity - reserved_quantity'));
    }

    public function adjustStock(StockItem $stockItem, int $quantity, string $reason, array $meta = []): StockItem
    {
        $beforeQuantity = $stockItem->quantity;

        DB::transaction(function () use ($stockItem, $quantity, $reason, $meta, $beforeQuantity) {
            $stockItem->update([
                'quantity' => $stockItem->quantity + $quantity
            ]);

            // Record movement
            $stockItem->movements()->create([
                'type' => $quantity > 0 ? 'in' : 'out',
                'quantity' => abs($quantity),
                'before_quantity' => $beforeQuantity,
                'after_quantity' => $stockItem->quantity,
                'reason' => $reason,
                'notes' => $meta['notes'] ?? null,
                'reference_type' => $meta['reference_type'] ?? 'adjustment',
                'reference_id' => $meta['reference_id'] ?? null,
            ]);
        });

        Event::dispatch('stock.adjusted', [$stockItem, $quantity, $reason]);

        if ($stockItem->is_low_stock) {
            Event::dispatch('stock.low', [$stockItem]);
        }

        if ($stockItem->is_out_of_stock) {
            Event::dispatch('stock.out', [$stockItem->product]);
        }

        return $stockItem->fresh();
    }

    public function reserveStock(Product $product, int $quantity, $reference, ?int $warehouseId = null): StockReservation
    {
        $stockItem = $this->getStockItemForProduct($product, $warehouseId);

        if (!$stockItem) {
            throw new \Exception('Stock item not found for this product');
        }

        if ($stockItem->available_quantity < $quantity) {
            throw new \Exception('Insufficient stock available for reservation');
        }

        $reservation = DB::transaction(function () use ($stockItem, $quantity, $reference) {
            $stockItem->increment('reserved_quantity', $quantity);

            $expiresAt = now()->addMinutes(
                config('arkenstone.stock.reservation_expires_minutes', 30)
            );

            $reservation = $stockItem->reservations()->create([
                'quantity' => $quantity,
                'order_id' => is_object($reference) ? $reference->id : null,
                'cart_id' => is_string($reference) ? $reference : null,
                'expires_at' => $expiresAt,
            ]);

            return $reservation;
        });

        Event::dispatch('stock.reserved', [$reservation]);

        return $reservation;
    }

    public function releaseReservation(StockReservation $reservation): void
    {
        if ($reservation->released_at) {
            return; // Already released
        }

        DB::transaction(function () use ($reservation) {
            $reservation->stockItem->decrement('reserved_quantity', $reservation->quantity);

            $reservation->update([
                'released_at' => now()
            ]);
        });

        Event::dispatch('stock.released', [$reservation]);
    }

    public function checkLowStock(): \Illuminate\Database\Eloquent\Collection
    {
        return StockItem::lowStock()
            ->with(['product', 'warehouse'])
            ->get();
    }

    protected function getStockItemForProduct(Product $product, ?int $warehouseId = null): ?StockItem
    {
        $query = StockItem::where('product_id', $product->id);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->first();
    }
}
```

---

## 🔗 Integration Between Modules

### Order-Stock Integration

When an order is created or updated, the stock system should be updated automatically through events.

**File:** `src/ECommerce/Order/Listeners/OrderStockListener.php`

```php
<?php

namespace Arkenstone\Core\ECommerce\Order\Listeners;

use Arkenstone\Core\Support\Event;
use Arkenstone\Core\ECommerce\Stock\Services\StockService;

class OrderStockListener
{
    protected StockService $stockService;

    public function __construct()
    {
        $this->stockService = app('stock');
    }

    public function register(): void
    {
        // Reserve stock when order is created
        Event::hook('order.created', function ($order) {
            $this->reserveStockForOrder($order);
        });

        // Deduct stock when payment is confirmed
        Event::hook('order.payment.received', function ($order) {
            $this->deductStockForOrder($order);
        });

        // Release stock when order is cancelled
        Event::hook('order.cancelled', function ($order) {
            $this->releaseStockForOrder($order);
        });

        // Return stock when order is refunded
        Event::hook('order.refunded', function ($order) {
            $this->returnStockForOrder($order);
        });
    }

    protected function reserveStockForOrder($order): void
    {
        foreach ($order->items as $item) {
            $this->stockService->reserveStock(
                $item->product,
                $item->quantity,
                $order
            );
        }
    }

    protected function deductStockForOrder($order): void
    {
        foreach ($order->items as $item) {
            $stockItem = $item->product->stockItem;
            
            if ($stockItem) {
                $this->stockService->adjustStock(
                    $stockItem,
                    -$item->quantity,
                    'Order payment confirmed',
                    [
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                    ]
                );
            }
        }
    }

    protected function releaseStockForOrder($order): void
    {
        $reservations = $order->stockReservations;
        
        foreach ($reservations as $reservation) {
            $this->stockService->releaseReservation($reservation);
        }
    }

    protected function returnStockForOrder($order): void
    {
        foreach ($order->items as $item) {
            $stockItem = $item->product->stockItem;
            
            if ($stockItem) {
                $this->stockService->adjustStock(
                    $stockItem,
                    $item->quantity,
                    'Order refunded',
                    [
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                    ]
                );
            }
        }
    }
}
```

Register the listener in `OrderServiceProvider`:

```php
public function boot(): void
{
    $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    
    // Register order-stock integration
    (new \Arkenstone\Core\ECommerce\Order\Listeners\OrderStockListener())->register();
}
```

---

## 🧪 Testing Strategy

### Order Module Tests

**File:** `tests/Feature/Order/OrderTest.php`

```php
<?php

namespace Arkenstone\Core\Tests\Feature\Order;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Order\Models\Order;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Brand;

class OrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();
    }

    /** @test */
    public function it_can_create_an_order()
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $orderData = [
            'customer_email' => 'test@example.com',
            'customer_name' => 'John Doe',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 100.00,
                ]
            ],
        ];

        $orderService = app('order');
        $order = $orderService->createOrder($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertNotEmpty($order->order_number);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(1, $order->items()->count());
    }

    /** @test */
    public function it_generates_unique_order_numbers()
    {
        $numbers = [];
        
        for ($i = 0; $i < 10; $i++) {
            $number = Order::generateOrderNumber();
            $this->assertNotContains($number, $numbers);
            $numbers[] = $number;
        }
    }

    /** @test */
    public function it_can_update_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $orderService = app('order');

        $updatedOrder = $orderService->updateOrderStatus($order, 'processing', 'Payment confirmed');

        $this->assertEquals('processing', $updatedOrder->status);
        $this->assertEquals(2, $updatedOrder->statusHistory()->count());
    }

    /** @test */
    public function it_can_cancel_an_order()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $orderService = app('order');

        $cancelledOrder = $orderService->cancelOrder($order, 'Customer request');

        $this->assertEquals('cancelled', $cancelledOrder->status);
        $this->assertNotNull($cancelledOrder->cancelled_at);
    }

    /** @test */
    public function it_cannot_cancel_completed_order()
    {
        $order = Order::factory()->create(['status' => 'completed']);
        $orderService = app('order');

        $this->expectException(\Exception::class);
        $orderService->cancelOrder($order);
    }
}
```

### Stock Module Tests

**File:** `tests/Feature/Stock/StockTest.php`

```php
<?php

namespace Arkenstone\Core\Tests\Feature\Stock;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\StockItem;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Brand;

class StockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();
    }

    /** @test */
    public function it_can_adjust_stock_levels()
    {
        $stockItem = StockItem::factory()->create(['quantity' => 100]);
        $stockService = app('stock');

        $updatedStock = $stockService->adjustStock($stockItem, 50, 'Stock replenishment');

        $this->assertEquals(150, $updatedStock->quantity);
        $this->assertEquals(1, $updatedStock->movements()->count());
    }

    /** @test */
    public function it_can_reserve_stock()
    {
        $stockItem = StockItem::factory()->create([
            'quantity' => 100,
            'reserved_quantity' => 0
        ]);
        
        $stockService = app('stock');

        $reservation = $stockService->reserveStock(
            $stockItem->product, 
            10, 
            'test-cart-123'
        );

        $this->assertEquals(10, $reservation->quantity);
        $this->assertEquals(10, $stockItem->fresh()->reserved_quantity);
        $this->assertEquals(90, $stockItem->fresh()->available_quantity);
    }

    /** @test */
    public function it_cannot_reserve_more_than_available()
    {
        $stockItem = StockItem::factory()->create([
            'quantity' => 10,
            'reserved_quantity' => 0
        ]);
        
        $stockService = app('stock');

        $this->expectException(\Exception::class);
        $stockService->reserveStock($stockItem->product, 20, 'test-cart');
    }

    /** @test */
    public function it_can_release_reservations()
    {
        $stockItem = StockItem::factory()->create([
            'quantity' => 100,
            'reserved_quantity' => 10
        ]);
        
        $reservation = $stockItem->reservations()->create([
            'quantity' => 10,
            'cart_id' => 'test-cart',
            'expires_at' => now()->addHour(),
        ]);

        $stockService = app('stock');
        $stockService->releaseReservation($reservation);

        $this->assertEquals(0, $stockItem->fresh()->reserved_quantity);
        $this->assertNotNull($reservation->fresh()->released_at);
    }

    /** @test */
    public function it_detects_low_stock()
    {
        StockItem::factory()->create([
            'quantity' => 5,
            'reserved_quantity' => 0,
            'reorder_point' => 10
        ]);

        $stockService = app('stock');
        $lowStock = $stockService->checkLowStock();

        $this->assertCount(1, $lowStock);
    }
}
```

---

## 📝 Code Examples

### Creating an Order from Cart

```php
$cartItems = [
    [
        'product_id' => 1,
        'quantity' => 2,
        'unit_price' => 29.99,
    ],
    [
        'product_id' => 2,
        'quantity' => 1,
        'unit_price' => 49.99,
    ]
];

$orderData = [
    'customer_id' => 5,
    'customer_email' => 'customer@example.com',
    'customer_name' => 'Jane Smith',
    'customer_phone' => '+1234567890',
    'items' => $cartItems,
    'shipping_amount' => 10.00,
    'discount_amount' => 5.00,
    'tax_rate' => 8.5, // 8.5%
    'payment_method' => 'credit_card',
    'billing_address_id' => 1,
    'shipping_address_id' => 2,
];

$orderService = app('order');
$order = $orderService->createOrder($orderData);
```

### Stock Management Workflow

```php
$stockService = app('stock');
$product = Product::find(1);

// Check available stock
$available = $stockService->getAvailableStock($product);

// Reserve stock for cart
$reservation = $stockService->reserveStock($product, 5, 'cart-abc123');

// Later, when order is placed
$stockItem = $product->stockItem;
$stockService->adjustStock($stockItem, -5, 'Order placed', [
    'reference_type' => 'order',
    'reference_id' => $order->id
]);

// Release the reservation
$stockService->releaseReservation($reservation);
```

---

## ✅ Implementation Checklist

### Order Module
- [ ] Create migrations for orders, order_items, order_status_history, addresses
- [ ] Create Order, OrderItem, OrderStatusHistory, Address models
- [ ] Create OrderService with all methods
- [ ] Create OrderController with CRUD operations
- [ ] Create Form Request validators
- [ ] Create API Resources for responses
- [ ] Define API routes
- [ ] Create OrderServiceProvider
- [ ] Register in CoreServiceProvider
- [ ] Write comprehensive tests (unit + feature)
- [ ] Update API documentation

### Stock Module
- [ ] Create migrations for stock_items, stock_movements, stock_reservations, warehouses
- [ ] Create StockItem, StockMovement, StockReservation, Warehouse models
- [ ] Create StockService with all methods
- [ ] Create StockController with operations
- [ ] Create Form Request validators
- [ ] Create API Resources
- [ ] Define API routes
- [ ] Create StockServiceProvider
- [ ] Register in CoreServiceProvider
- [ ] Write comprehensive tests
- [ ] Update API documentation

### Integration
- [ ] Create OrderStockListener for event integration
- [ ] Test order creation → stock reservation flow
- [ ] Test order payment → stock deduction flow
- [ ] Test order cancellation → stock release flow
- [ ] Test order refund → stock return flow
- [ ] Add integration tests

---

**Document Version:** 1.0.0  
**Last Updated:** December 15, 2025  
**Status:** Implementation Ready
