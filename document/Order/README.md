# Order Module - Activity Diagrams Documentation

**Arkenstone Core Package - Order Management System**  
**Version:** 1.0  
**Date:** December 22, 2025  
**Format:** PlantUML (Draw.io Compatible)

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture Review](#architecture-review)
3. [Activity Diagrams](#activity-diagrams)
4. [Missing Elements Addressed](#missing-elements-addressed)
5. [Database Schema Requirements](#database-schema-requirements)
6. [Implementation Checklist](#implementation-checklist)
7. [Integration Points](#integration-points)
8. [Usage Instructions](#usage-instructions)

---

## Overview

This document contains comprehensive activity diagrams for the **Order Module** of the Arkenstone Core e-commerce package. The diagrams cover the complete order lifecycle from cart creation to order completion, including payment processing with gateway adapter pattern.

### Diagram Files

| File | Description | Key Features |
|------|-------------|--------------|
| `01_Add_to_Cart_Flow.puml` | Add products to cart workflow | Guest/logged distinction, stock validation, event system |
| `02_Checkout_Process_Flow.puml` | Checkout and order preparation | Address management, shipping calculation, payment method selection |
| `03_Order_Placement_Flow.puml` | Order creation and stock locking | Transaction management, order snapshots, stock reservation |
| `04_Payment_Processing_Flow.puml` | Payment gateway integration | Adapter pattern, webhook handling, multi-method support |
| `05_Order_Status_Management_Flow.puml` | Order lifecycle and status updates | Status progression, event triggers, automated transitions |
| `06_Guest_to_Logged_Cart_Migration_Flow.puml` | Cart migration on login/registration | Merge logic, conflict resolution, data preservation |
| `07_Order_Cancellation_Flow.puml` | Order cancellation and refunds | Eligibility checks, stock restoration, refund processing |

---

## Architecture Review

### Your Original Design Strengths ✅

1. **Clear module separation** - Cart, Order, Payment as distinct entities
2. **Guest vs Logged Cart distinction** - Critical for UX and data management
3. **Payment Gateway Adapter pattern** - Excellent extensibility for multiple gateways
4. **Multiple payment methods** - Card, COD, Bank Transfer coverage
5. **Comprehensive use case coverage** - Full order lifecycle represented

### Critical Missing Points Identified and Addressed ⚠️

#### 1. **User/Customer Management** ✓ ADDED
- **Added:** User authentication flow in all diagrams
- **Added:** Guest-to-logged migration workflow (Diagram 06)
- **Added:** Authorization checks before order operations
- **Solution:** Clear separation of guest (user_id = NULL) vs logged users (user_id = foreign key)

#### 2. **Address Management** ✓ ADDED
- **Added:** Shipping and billing address collection (Diagram 02)
- **Added:** Address validation steps
- **Added:** Separate address tables for order history preservation
- **Solution:** `order_shipping_addresses` and `order_billing_addresses` tables

#### 3. **Order Status Workflow** ✓ ADDED
- **Added:** Complete status lifecycle (Diagram 05)
- **Added:** Status progression: pending_payment → confirmed → processing → shipped → delivered → completed
- **Added:** Cancellation flow (Diagram 07)
- **Added:** Automated status transitions with scheduled jobs
- **Solution:** Order status enum and payment status tracking

#### 4. **Inventory/Stock Integration** ✓ ADDED
- **Added:** Stock validation at cart addition (Diagram 01)
- **Added:** Stock locking during order placement (Diagram 03)
- **Added:** Stock restoration on cancellation (Diagram 07)
- **Added:** Temporary stock reservations for cart items
- **Solution:** Integration with StockService and stock_movements table

#### 5. **Cart Operations Detail** ✓ ADDED
- **Added:** Update cart item quantity flow in merge logic
- **Added:** Remove from cart (implicit in migration)
- **Added:** Cart validation before checkout
- **Added:** 7-day guest cart expiry with cleanup jobs
- **Solution:** Cart status tracking and scheduled cleanup

#### 6. **Payment Processing** ✓ ADDED
- **Added:** Payment status tracking entity (Diagram 04)
- **Added:** Transaction records in payment table
- **Added:** Payment retry mechanism (failed payment flows)
- **Added:** Webhook handling for async payments with signature verification
- **Solution:** Payment model with gateway integration via adapter pattern

#### 7. **Order Item Details** ✓ ADDED
- **Added:** Product data snapshot at order time (Diagram 03)
- **Added:** Discount application at item level
- **Added:** Tax calculation per item and order total
- **Solution:** `order_items` table with complete product snapshot JSON

#### 8. **Notification System** ✓ ADDED
- **Added:** Event dispatch at all critical points
- **Added:** Order confirmation events (OrderCreated, OrderConfirmed)
- **Added:** Payment events (PaymentCompleted, PaymentFailed)
- **Added:** Status change events (OrderShipped, OrderDelivered, OrderCancelled)
- **Solution:** Arkenstone Event system integration at each workflow stage

---

## Activity Diagrams

### 01. Add to Cart Flow

**Purpose:** Handle adding products to cart for both guest and logged users

**Key Decision Points:**
- User authenticated? → Branch to logged/guest cart handler
- Product exists in cart? → Update quantity vs add new item
- Stock available? → Success vs error response

**Critical Features:**
- Session-based guest carts with 7-day expiry
- Database-backed logged user carts
- Product snapshot with current price
- Optional soft stock reservation (30 minutes)
- Event dispatch: `CartItemAdded`, `CartItemUpdated`

**Response Protocol:**
```json
{
  "status": "success",
  "message": "Product added to cart",
  "data": {
    "cart": { "id": 1, "items_count": 3, "total": 150.00 },
    "item": { "id": 5, "product_id": 10, "quantity": 2 }
  }
}
```

### 02. Checkout Process Flow

**Purpose:** Prepare order from cart, collect addresses, calculate totals

**Key Decision Points:**
- Cart has items? → Proceed vs error
- All items valid? → Continue vs show validation errors
- Same billing address? → Copy shipping vs collect separately

**Critical Features:**
- Item-by-item validation (price changes, stock, product status)
- Saved addresses for logged users
- Shipping method calculation based on destination
- Order summary with all costs (subtotal, tax, shipping, discount)
- 15-minute checkout session expiry
- Checkout token generation for order placement

**Integration Points:**
- ProductService: Validate products and prices
- AddressService: Fetch saved addresses
- ShippingService: Calculate shipping options

### 03. Order Placement Flow

**Purpose:** Convert cart to order with atomic stock reservation

**Key Decision Points:**
- Checkout token valid? → Proceed vs restart
- Stock available for all items? → Lock and proceed vs error
- Payment method? → Branch to COD/Bank Transfer/Card flow

**Critical Features:**
- Database transaction for atomicity
- Row-level stock locking to prevent overselling
- Order number generation (configurable format)
- Complete product data snapshot in order_items
- Separate shipping/billing address records
- Event dispatch: `OrderCreated`, `OrderConfirmed` (for COD)

**Stock Operations:**
```sql
UPDATE products
SET stock_quantity = stock_quantity - ordered_qty
WHERE id = ? AND stock_quantity >= ordered_qty

INSERT INTO stock_movements
(product_id, type, quantity, reference_type, reference_id)
VALUES (10, 'order', -5, 'order', 1)
```

### 04. Payment Processing Flow with Adapter Pattern

**Purpose:** Process payments through multiple gateways using adapter pattern

**Key Decision Points:**
- Payment method? → Route to Card/COD/Bank Transfer handler
- Gateway adapter exists? → Proceed vs configuration error
- Gateway response success? → Initiate payment vs error
- Webhook signature valid? → Process vs reject (security)
- Payment status? → Update order accordingly

**Critical Features:**

#### Payment Adapter Interface
```php
interface PaymentAdapterInterface {
    public function createPayment(array $data): PaymentResponse;
    public function capturePayment(string $paymentId): PaymentResponse;
    public function refundPayment(string $paymentId, float $amount): PaymentResponse;
    public function getPaymentStatus(string $paymentId): PaymentResponse;
    public function handleWebhook(Request $request): WebhookResponse;
}
```

#### Gateway Configuration
```php
'payment' => [
    'default_gateway' => env('PAYMENT_GATEWAY', 'stripe'),
    'gateways' => [
        'stripe' => [
            'adapter' => StripeAdapter::class,
            'key' => env('STRIPE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        // Developers add their own adapters here
    ]
]
```

#### Developer Integration Steps
1. Create adapter class implementing `PaymentAdapterInterface`
2. Add configuration to `config/arkenstone.php`
3. No core module changes required
4. Gateway-specific logic contained in adapter

#### Webhook Security
- Signature verification required
- Gateway-specific validation (Stripe, PayPal, etc.)
- Return 401 for invalid signatures
- Log security warnings

#### Event Dispatch
- `PaymentInitiated`: When payment starts
- `PaymentCompleted`: On successful payment
- `PaymentFailed`: On payment failure

### 05. Order Status Management Flow

**Purpose:** Manage order lifecycle and status transitions

**Status Progression:**
```
pending_payment → confirmed → processing → shipped → delivered → completed
                     ↓
                  cancelled (any time before shipped)
                     ↓
                  returned (after delivered)
```

**Key Decision Points:**
- Payment completed? → Move to confirmed
- Admin updates status? → Validate transition and proceed
- Delivery confirmed? → Auto-complete or manual
- Return window expired? → Auto-complete

**Critical Features:**

#### Status States and Triggers
| Status | Trigger | Event Dispatched | Actions |
|--------|---------|------------------|---------|
| `pending_payment` | Order created | `OrderCreated` | Wait for payment |
| `confirmed` | Payment received | `OrderConfirmed` | Notify warehouse |
| `processing` | Admin update | `OrderProcessing` | Pick and pack |
| `shipped` | Admin ships | `OrderShipped` | Send tracking |
| `delivered` | Carrier/Admin | `OrderDelivered` | Start return window |
| `completed` | Auto (7 days) | `OrderCompleted` | Finalize |
| `cancelled` | User/Admin | `OrderCancelled` | Refund, restore stock |

#### Automated Jobs
- **Payment timeout:** Cancel orders with pending payment > 48 hours
- **Auto-complete:** Mark delivered orders as completed after 7 days
- **Reminder emails:** 24h, 36h, 48h for pending payments
- **Guest cart cleanup:** Delete expired session carts > 7 days

#### COD Special Handling
- Order status: `confirmed` immediately after placement
- Payment status: `cod_pending` until delivery
- On delivery: Update payment status to `paid`

### 06. Guest to Logged User Cart Migration Flow

**Purpose:** Preserve cart when guest logs in or registers

**Key Decision Points:**
- Session has cart token? → Migrate vs skip
- Product in both carts? → Merge quantities vs add new
- Stock available for merged quantity? → Update vs cap at max

**Critical Features:**

#### Migration Trigger Points
1. Successful login
2. Successful registration
3. Social auth completion

#### Merge Logic
```
Scenario 1: Same Product
User cart: Product A × 2
Guest cart: Product A × 3
Result: Product A × 5 (if stock available)

Scenario 2: Different Products
User cart: Product A × 2
Guest cart: Product B × 1
Result: Product A × 2, Product B × 1

Scenario 3: Stock Conflict
User cart: Product A × 3
Guest cart: Product A × 5
Available stock: 6
Result: Product A × 6 (capped), warning shown
```

#### Error Handling
- Product no longer available → Skip, add to warnings
- Out of stock → Skip, add to warnings
- Price changed → Update with new price, notify user
- Product deactivated → Skip, add to warnings

#### Guest Cart Preservation
- Don't delete guest cart (audit trail)
- Mark as `migrated` status
- Link to user cart: `migrated_to_cart_id`
- Timestamp: `migrated_at`

#### Event Dispatch
```php
Event::dispatch('cart.migrated', [
    'guest_cart_id' => 5,
    'user_cart_id' => 10,
    'user_id' => 1,
    'items_merged' => 3,
    'items_added' => 2,
    'items_skipped' => 1,
    'migration_errors' => [...]
])
```

### 07. Order Cancellation Flow

**Purpose:** Handle order cancellations and refunds

**Key Decision Points:**
- Order status allows cancellation? → Proceed vs error (shipped/delivered cannot cancel)
- User authorized? → Customer owns order or admin
- Payment completed? → Initiate refund vs just cancel
- Payment method? → Gateway refund vs manual vs no refund (COD)

**Critical Features:**

#### Cancellable Statuses
✅ Can cancel:
- `pending_payment`
- `confirmed`
- `processing`

❌ Cannot cancel (use return flow):
- `shipped`
- `delivered`
- `completed`

#### Refund Handling by Payment Method

**Card/Gateway Payment:**
```php
$adapter->refundPayment(
    $payment->gateway_payment_id,
    $order->grand_total
)
```
- Automatic refund via gateway
- Status: `refunding` → `refunded`
- Timeline: 5-7 business days

**Bank Transfer:**
- Manual refund by admin
- Status: `pending_manual`
- Admin processes bank transfer back
- Admin updates status manually

**COD:**
- No refund needed (payment not collected)
- Just cancel payment record

#### Stock Restoration
```php
// For each order item
StockService::restoreStock($orderItem);

// Updates
UPDATE products
SET stock_quantity = stock_quantity + returned_qty
WHERE id = product_id

INSERT INTO stock_movements
(product_id, type, quantity, reference_type, reference_id)
VALUES (10, 'cancellation', 5, 'order', 1)
```

#### Authorization
- **Customer:** Can only cancel their own orders
- **Admin:** Can cancel any order
- Record who cancelled: `cancelled_by`, `cancelled_by_type`

---

## Missing Elements Addressed

### Summary Table

| Missing Element | Diagram(s) | Solution |
|----------------|-----------|----------|
| User authentication flow | 01, 02, 03, 06 | Auth gates and guest/logged branching |
| Guest-to-logged migration | 06 | Dedicated migration workflow |
| Address management | 02, 03 | Shipping/billing address collection |
| Order status workflow | 05, 07 | Complete lifecycle with events |
| Stock integration | 01, 03, 07 | Validation, locking, restoration |
| Cart operations | 01, 06 | Add, update, merge, validate |
| Payment status tracking | 03, 04, 05 | Payment model with status |
| Transaction records | 04 | Gateway transactions logged |
| Payment retry | 04 | Failed payment handling |
| Webhooks | 04 | Signature-verified async handling |
| Product snapshots | 03 | Order items preserve product data |
| Discount application | 01, 03 | Per-item discount calculations |
| Tax calculation | 01, 02, 03 | Item-level and order-level tax |
| Notifications | All | Event system at every stage |
| Order confirmation | 03, 04, 05 | OrderConfirmed event |
| Payment notifications | 04 | PaymentCompleted/Failed events |
| Status change alerts | 05 | Event on each transition |

---

## Database Schema Requirements

### Core Tables

#### 1. **carts**
```sql
CREATE TABLE carts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULL,                    -- NULL for guest carts
    cart_token VARCHAR(64) UNIQUE NULL,     -- For guest cart identification
    session_id VARCHAR(128) NULL,           -- Laravel session ID
    status ENUM('active', 'completed', 'migrated', 'expired') DEFAULT 'active',
    order_id BIGINT NULL,                   -- Link to created order
    migrated_to_cart_id BIGINT NULL,        -- For guest→logged migration
    expires_at TIMESTAMP NULL,              -- 7 days from creation for guests
    completed_at TIMESTAMP NULL,
    migrated_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_cart_token (cart_token),
    INDEX idx_session_id (session_id),
    INDEX idx_user_status (user_id, status)
);
```

#### 2. **cart_items**
```sql
CREATE TABLE cart_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cart_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    product_sku VARCHAR(100),
    product_name VARCHAR(255),
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    discount_type ENUM('percentage', 'fixed', 'none') DEFAULT 'none',
    discount_value DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    total_price DECIMAL(10, 2) NOT NULL,
    product_snapshot JSON NULL,             -- Attributes, image, etc.
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_cart_product (cart_id, product_id)
);
```

#### 3. **orders**
```sql
CREATE TABLE orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id BIGINT NULL,                    -- NULL for guest orders
    guest_email VARCHAR(255) NULL,          -- For guest order tracking
    cart_id BIGINT NULL,
    
    status ENUM('pending_payment', 'confirmed', 'processing', 'shipped', 
                'delivered', 'completed', 'cancelled', 'returned') DEFAULT 'pending_payment',
    payment_status ENUM('pending', 'cod_pending', 'awaiting_payment', 'payment_initiated',
                        'paid', 'failed', 'refunding', 'refunded', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('card', 'cod', 'bank_transfer') NOT NULL,
    
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    grand_total DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    
    notes TEXT NULL,
    admin_notes TEXT NULL,
    
    confirmed_at TIMESTAMP NULL,
    processing_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    
    cancelled_at TIMESTAMP NULL,
    cancelled_by BIGINT NULL,
    cancelled_by_type ENUM('customer', 'admin') NULL,
    cancellation_reason VARCHAR(255) NULL,
    cancellation_note TEXT NULL,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_user_id (user_id),
    INDEX idx_guest_email (guest_email),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status)
);
```

#### 4. **order_items**
```sql
CREATE TABLE order_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    product_sku VARCHAR(100),
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    discount_type ENUM('percentage', 'fixed', 'none') DEFAULT 'none',
    discount_value DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    subtotal DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    product_snapshot JSON NOT NULL,         -- Full product data at order time
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id)
);
```

#### 5. **order_shipping_addresses**
```sql
CREATE TABLE order_shipping_addresses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);
```

#### 6. **order_billing_addresses**
```sql
CREATE TABLE order_billing_addresses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);
```

#### 7. **payments**
```sql
CREATE TABLE payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    payment_method ENUM('card', 'cod', 'bank_transfer') NOT NULL,
    gateway VARCHAR(50) NULL,               -- stripe, paypal, razorpay, etc.
    gateway_payment_id VARCHAR(255) NULL,   -- External payment ID
    transaction_id VARCHAR(255) NULL,       -- Bank/gateway transaction ID
    
    status ENUM('initiated', 'pending', 'cod_pending', 'awaiting_transfer',
                'completed', 'failed', 'refunding', 'refunded', 'cancelled') DEFAULT 'pending',
    
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    
    redirect_url TEXT NULL,                 -- For card payments
    gateway_response JSON NULL,             -- Full gateway response
    failed_reason TEXT NULL,
    
    paid_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    refunded_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_gateway_payment_id (gateway_payment_id),
    INDEX idx_status (status)
);
```

#### 8. **payment_refunds**
```sql
CREATE TABLE payment_refunds (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    payment_id BIGINT NOT NULL,
    refund_amount DECIMAL(10, 2) NOT NULL,
    refund_method VARCHAR(50) NOT NULL,     -- original_payment_method, bank_transfer, etc.
    gateway_refund_id VARCHAR(255) NULL,
    status ENUM('pending', 'pending_manual', 'completed', 'failed') DEFAULT 'pending',
    reason TEXT NULL,
    initiated_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    estimated_completion DATE NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    INDEX idx_payment_id (payment_id)
);
```

#### 9. **order_status_history**
```sql
CREATE TABLE order_status_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    from_status VARCHAR(50) NULL,
    to_status VARCHAR(50) NOT NULL,
    changed_by BIGINT NULL,
    changed_by_type ENUM('system', 'customer', 'admin') DEFAULT 'system',
    note TEXT NULL,
    created_at TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);
```

---

## Implementation Checklist

### Phase 1: Database & Models (Week 1-2)

- [ ] Create all migration files (9 tables)
- [ ] Create Eloquent models with relationships
- [ ] Create model factories for testing
- [ ] Create database seeders
- [ ] Add model scopes for common queries
- [ ] Implement model events (creating, created, updating, updated)

### Phase 2: Service Layer (Week 2-3)

- [ ] Create `OrderServiceProvider`
- [ ] Implement `CartService` with methods:
  - [ ] `addToCart()`
  - [ ] `updateCartItem()`
  - [ ] `removeFromCart()`
  - [ ] `getCart()`
  - [ ] `clearCart()`
  - [ ] `validateCart()`
  - [ ] `migrateGuestCart()`
- [ ] Implement `OrderService` with methods:
  - [ ] `createOrder()`
  - [ ] `getOrder()`
  - [ ] `updateOrderStatus()`
  - [ ] `cancelOrder()`
  - [ ] `getOrderHistory()`
- [ ] Implement `PaymentService` with methods:
  - [ ] `initiatePayment()`
  - [ ] `processWebhook()`
  - [ ] `refundPayment()`
  - [ ] `getPaymentStatus()`
- [ ] Implement `CheckoutService` with methods:
  - [ ] `initiateCheckout()`
  - [ ] `calculateShipping()`
  - [ ] `calculateTotals()`
  - [ ] `validateCheckout()`

### Phase 3: Payment Adapter Pattern (Week 3)

- [ ] Create `PaymentAdapterInterface`
- [ ] Create `PaymentResponse` value object
- [ ] Create `WebhookResponse` value object
- [ ] Implement `StripeAdapter` (example)
- [ ] Create adapter configuration structure
- [ ] Document adapter creation for developers
- [ ] Add gateway resolver logic

### Phase 4: HTTP Layer (Week 4)

- [ ] Create controllers:
  - [ ] `CartController`
  - [ ] `CheckoutController`
  - [ ] `OrderController`
  - [ ] `PaymentWebhookController`
- [ ] Create Form Requests for validation:
  - [ ] `AddToCartRequest`
  - [ ] `UpdateCartItemRequest`
  - [ ] `CheckoutRequest`
  - [ ] `PlaceOrderRequest`
  - [ ] `CancelOrderRequest`
- [ ] Create API Resources:
  - [ ] `CartResource`
  - [ ] `CartItemResource`
  - [ ] `OrderResource`
  - [ ] `OrderItemResource`
  - [ ] `PaymentResource`
- [ ] Define API routes in `routes/api.php`

### Phase 5: Event System (Week 4-5)

- [ ] Create event classes:
  - [ ] `CartItemAdded`
  - [ ] `CartItemUpdated`
  - [ ] `CartMigrated`
  - [ ] `OrderCreated`
  - [ ] `OrderConfirmed`
  - [ ] `OrderProcessing`
  - [ ] `OrderShipped`
  - [ ] `OrderDelivered`
  - [ ] `OrderCompleted`
  - [ ] `OrderCancelled`
  - [ ] `PaymentInitiated`
  - [ ] `PaymentCompleted`
  - [ ] `PaymentFailed`
- [ ] Integrate with Arkenstone Event system
- [ ] Create example event listeners
- [ ] Document event hooks for developers

### Phase 6: Scheduled Jobs (Week 5)

- [ ] Create jobs:
  - [ ] `CleanupExpiredGuestCarts` (daily)
  - [ ] `CancelPendingPaymentOrders` (hourly)
  - [ ] `AutoCompleteDeliveredOrders` (daily)
  - [ ] `SendPaymentReminders` (hourly)
- [ ] Register jobs in service provider
- [ ] Add configuration for job schedules

### Phase 7: Testing (Week 5-6)

- [ ] Unit tests for models
- [ ] Unit tests for services
- [ ] Unit tests for adapters
- [ ] Feature tests for API endpoints:
  - [ ] Cart management
  - [ ] Checkout process
  - [ ] Order placement
  - [ ] Order cancellation
  - [ ] Payment webhooks
- [ ] Integration tests for workflows
- [ ] Test guest-to-logged migration
- [ ] Test payment adapter pattern

### Phase 8: Documentation (Week 6)

- [ ] API documentation (endpoints, requests, responses)
- [ ] Payment adapter creation guide
- [ ] Configuration documentation
- [ ] Event listener examples
- [ ] Migration guide
- [ ] Deployment checklist

---

## Integration Points

### With Product Module

```php
// In CartService
$product = app('product')->getProductById($productId);
$stockAvailable = app('product')->checkStock($productId, $quantity);

// In OrderService
$product = Product::with('images', 'brand', 'categories')->find($productId);
```

### With Stock Module

```php
// In CartService (optional soft reservation)
StockService::reserveTemporary($productId, $quantity, $cartId, 30); // 30 minutes

// In OrderService (hard reservation)
StockService::deductStock($productId, $quantity, 'order', $orderId);

// In OrderService (cancellation)
StockService::restoreStock($productId, $quantity, 'cancellation', $orderId);
```

### With User/Auth

```php
// In CartController
$userId = auth()->id(); // NULL for guests
$cart = $this->cartService->getCart($userId);

// In CheckoutController
if (auth()->check()) {
    $savedAddresses = auth()->user()->addresses;
}
```

### With Event System

```php
// In OrderService
use Arkenstone\Core\Support\Event;

Event::dispatch('order.created', [$order, $orderItems]);

// Developers listen to events
Event::hook('order.created', function($order, $orderItems) {
    // Send email, update analytics, etc.
});
```

### With ResponseProtocol

```php
// In all controllers
use Arkenstone\Core\Helpers\ResponseProtocol;

return ResponseProtocol::success($data, 'Order placed successfully', 201);
return ResponseProtocol::error($errors, 'Validation failed', 422);
```

---

## Usage Instructions

### How to Use These Diagrams

#### 1. **Import into Draw.io**

1. Open [Draw.io](https://app.diagrams.net/)
2. Click **File → Import from → Text**
3. Paste the contents of any `.puml` file
4. Select format: **PlantUML**
5. Click **Import**
6. The diagram will render automatically

#### 2. **View Locally**

```bash
# Install PlantUML
brew install plantuml  # macOS
# or
sudo apt-get install plantuml  # Linux

# Generate PNG
plantuml 01_Add_to_Cart_Flow.puml

# Generate SVG
plantuml -tsvg 01_Add_to_Cart_Flow.puml

# Generate all diagrams
plantuml *.puml
```

#### 3. **Edit Diagrams**

- Open `.puml` files in any text editor
- Modify the PlantUML syntax
- Re-import to Draw.io to see changes
- Use VS Code with PlantUML extension for live preview

#### 4. **VS Code Setup**

```bash
# Install extension
code --install-extension jebbs.plantuml

# Open .puml file
# Press Alt+D to preview
```

### Understanding Diagram Notation

#### Swimlanes (|Name|)
```plantuml
|Customer|        # Actor/user swimlane
|#LightBlue|Service|  # Colored service swimlane
```

#### Decision Points (if/then/else)
```plantuml
if (Condition?) then (yes)
  :Action if true;
else (no)
  :Action if false;
endif
```

#### Notes
```plantuml
note right
  **Bold text**
  Detailed explanation
end note
```

#### Partitions
```plantuml
partition "Group Name" {
  :Action 1;
  :Action 2;
}
```

---

## Configuration Example

Add to `config/arkenstone.php`:

```php
return [
    // Existing config...
    
    'order' => [
        'enabled' => env('ARKENSTONE_ORDER_ENABLED', true),
        
        // Order number format
        'order_number_format' => 'ORD-{date}-{sequence}', // ORD-20251222-0001
        
        // Cart settings
        'cart' => [
            'guest_expiry_days' => 7,
            'stock_reservation_minutes' => 30,
            'max_items_per_cart' => 100,
        ],
        
        // Checkout settings
        'checkout' => [
            'session_expiry_minutes' => 15,
            'require_phone' => true,
            'require_company' => false,
        ],
        
        // Order settings
        'order' => [
            'payment_timeout_hours' => 48,
            'auto_complete_days' => 7,
            'cancellable_statuses' => ['pending_payment', 'confirmed', 'processing'],
        ],
        
        // Payment settings
        'payment' => [
            'default_gateway' => env('PAYMENT_GATEWAY', 'stripe'),
            'default_currency' => 'USD',
            'gateways' => [
                'stripe' => [
                    'adapter' => \App\Payment\Adapters\StripeAdapter::class,
                    'key' => env('STRIPE_KEY'),
                    'secret' => env('STRIPE_SECRET'),
                    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
                ],
                // Add more gateways...
            ],
        ],
        
        // Notification settings
        'notifications' => [
            'order_confirmation' => true,
            'payment_confirmation' => true,
            'order_shipped' => true,
            'order_delivered' => true,
        ],
    ],
];
```

---

## Next Steps

1. **Review diagrams with team** - Ensure all stakeholders understand flows
2. **Validate with Product Owner** - Confirm business logic matches requirements
3. **Start Phase 1 implementation** - Begin with database and models
4. **Create sample adapter** - Build Stripe adapter as reference
5. **Write tests alongside code** - TDD approach for reliability
6. **Document as you go** - Keep API docs updated with implementation

---

## Questions & Considerations

### For Discussion

1. **Guest cart persistence:** Should we use database or Redis for guest carts? (Current: database)
2. **Stock reservation:** Hard lock immediately or soft reservation until payment? (Current: soft during cart, hard during order)
3. **Order number format:** What format does the business prefer? (Current: configurable)
4. **Shipping integration:** Will we integrate with shipping APIs (FedEx, UPS) or manual entry? (Current: manual)
5. **Multi-currency:** Support multiple currencies? (Current: single currency USD)
6. **Tax calculation:** Simple flat rate or complex tax service (Avalara, TaxJar)? (Current: simple)
7. **Partial refunds:** Support partial refunds or only full? (Current: full refund in diagrams)
8. **Return flow:** Separate from cancellation? (Current: mentioned but not diagrammed)

### Performance Considerations

- **Cart operations:** Consider Redis caching for active carts
- **Stock locking:** Use row-level locks to prevent race conditions
- **Webhook processing:** Queue webhook processing for better response time
- **Event dispatch:** Consider queued event listeners for non-critical actions
- **Order queries:** Add appropriate indexes for common searches

---

## Conclusion

These activity diagrams provide a comprehensive blueprint for implementing the Order Module in Arkenstone Core. They address all missing elements identified in the original design and follow the established architectural patterns of the package.

The diagrams are:
- ✅ Draw.io compatible (PlantUML format)
- ✅ Comprehensive (7 major workflows)
- ✅ Detailed (includes all decision points, data structures, events)
- ✅ Aligned with Arkenstone architecture (service providers, event system, ResponseProtocol)
- ✅ Production-ready (includes security, validation, error handling)
- ✅ Extensible (payment adapter pattern for developers)

**Author:** GitHub Copilot (Claude Sonnet 4.5)  
**Review Status:** Ready for team review and implementation  
**Last Updated:** December 22, 2025

---
