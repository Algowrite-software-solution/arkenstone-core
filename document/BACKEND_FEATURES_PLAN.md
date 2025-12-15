# Backend Features Plan - Arkenstone Core E-Commerce Package

**Version:** 1.0.0  
**Created:** December 15, 2025  
**Status:** Planning Phase

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Current State Analysis](#current-state-analysis)
3. [Planned Feature Modules](#planned-feature-modules)
4. [Order Management System](#order-management-system)
5. [Stock/Inventory Management](#stockinventory-management)
6. [Customer Management](#customer-management)
7. [Cart & Checkout](#cart--checkout)
8. [Payment Gateway Integration](#payment-gateway-integration)
9. [Shipping & Fulfillment](#shipping--fulfillment)
10. [Discount & Coupon System](#discount--coupon-system)
11. [Review & Rating System](#review--rating-system)
12. [Wishlist Feature](#wishlist-feature)
13. [Search & Filter Enhancement](#search--filter-enhancement)
14. [Analytics & Reporting](#analytics--reporting)
15. [Implementation Roadmap](#implementation-roadmap)
16. [Database Schema Planning](#database-schema-planning)
17. [API Endpoint Planning](#api-endpoint-planning)
18. [Technical Considerations](#technical-considerations)

---

## 📖 Overview

This document outlines the comprehensive backend features planned for the Arkenstone Core e-commerce package. The plan builds upon the existing Product Management module to create a full-featured e-commerce solution.

**Design Principles:**
- Follow Laravel best practices and existing package patterns
- Maintain modular architecture with dedicated service providers
- Ensure backward compatibility with existing implementations
- Use ResponseProtocol for consistent API responses
- Implement event-driven architecture for extensibility
- Support soft deletes across all entities

---

## 🔍 Current State Analysis

### Implemented Modules ✅

**Product Management (v0.2.0)**
- ✅ Product CRUD with advanced filtering
- ✅ Brand management
- ✅ Category management (hierarchical)
- ✅ Taxonomy system (flexible attributes)
- ✅ Product image management
- ✅ Product-taxonomy relationships
- ✅ Comprehensive test coverage (176 tests)

**Infrastructure**
- ✅ Service provider architecture
- ✅ Event system (WordPress-style hooks)
- ✅ ResponseProtocol for standardized responses
- ✅ Orchestra Testbench for testing
- ✅ Migration system
- ✅ Factory and seeder support

### Pending Modules 🚧

**Order Module**
- 📁 Directory exists: `src/ECommerce/Order/`
- ⚠️ Status: Empty (placeholder)

**Stock Module**
- 📁 Directory exists: `src/ECommerce/Stock/`
- ⚠️ Status: Empty (placeholder)

### Architecture Patterns to Follow

Based on the existing Product module, new modules should implement:

1. **Directory Structure:**
   ```
   src/ECommerce/{Module}/
   ├── Http/
   │   ├── Controllers/API/V1/
   │   ├── Requests/
   │   └── Resources/
   ├── Models/
   ├── Services/
   ├── Provider/
   │   └── {Module}ServiceProvider.php
   └── routes/
       └── api.php
   ```

2. **Service Provider Pattern:**
   - Register services as singletons
   - Use simple string keys (e.g., 'order', 'stock')
   - Implement `Arkenstone\Core\ECommerce\Contracts\Service`

3. **Model Patterns:**
   - Use query scopes for filtering
   - Implement soft deletes
   - Define clear relationships
   - Use accessors for computed attributes

4. **Controller Patterns:**
   - Use Form Request validation
   - Return ResponseProtocol responses
   - Eager load relationships
   - Support pagination

---

## 🛒 Planned Feature Modules

### Priority 1: Essential E-Commerce Features
1. **Order Management System** 🔥
2. **Stock/Inventory Management** 🔥
3. **Customer Management** 🔥
4. **Cart & Checkout** 🔥

### Priority 2: Enhanced Shopping Experience
5. **Payment Gateway Integration**
6. **Shipping & Fulfillment**
7. **Discount & Coupon System**
8. **Review & Rating System**

### Priority 3: Additional Features
9. **Wishlist Feature**
10. **Search & Filter Enhancement**
11. **Analytics & Reporting**
12. **Notification System**

---

## 📦 Order Management System

### Overview
Complete order lifecycle management from creation to fulfillment, including status tracking, payment processing, and order history.

### Core Features

#### 1. Order Creation & Management
- Create orders from cart
- Manual order creation (admin)
- Order editing (before fulfillment)
- Order cancellation
- Order status workflow

#### 2. Order Status Management
**Status Flow:**
```
Pending → Processing → Shipped → Delivered → Completed
         ↓
     Cancelled/Refunded
```

**Status Definitions:**
- `pending`: Order placed, awaiting payment
- `processing`: Payment confirmed, preparing items
- `shipped`: Order dispatched to customer
- `delivered`: Order received by customer
- `completed`: Transaction finalized
- `cancelled`: Order cancelled by customer/admin
- `refunded`: Payment returned to customer
- `on_hold`: Awaiting additional action

#### 3. Order Items Management
- Line items with product details
- Quantity and pricing per item
- Product snapshots (price, name at order time)
- Support for product variants
- Tax calculation per item

#### 4. Order Calculations
- Subtotal (sum of items)
- Tax calculation
- Shipping fees
- Discounts/coupons applied
- Grand total

### Database Schema

#### `orders` Table
```php
- id: bigint (PK)
- order_number: string(50) unique
- customer_id: bigint (FK to customers)
- customer_email: string
- customer_name: string
- customer_phone: string
- status: enum (pending, processing, shipped, delivered, completed, cancelled, refunded, on_hold)
- payment_status: enum (unpaid, paid, partial, refunded)
- payment_method: string
- subtotal: decimal(10,2)
- tax_amount: decimal(10,2)
- shipping_amount: decimal(10,2)
- discount_amount: decimal(10,2)
- total_amount: decimal(10,2)
- currency: string(3) default 'USD'
- notes: text
- billing_address_id: bigint (FK to addresses)
- shipping_address_id: bigint (FK to addresses)
- shipped_at: timestamp
- delivered_at: timestamp
- cancelled_at: timestamp
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp
```

#### `order_items` Table
```php
- id: bigint (PK)
- order_id: bigint (FK to orders)
- product_id: bigint (FK to products)
- product_name: string (snapshot)
- product_sku: string (snapshot)
- product_image: string (snapshot)
- quantity: integer
- unit_price: decimal(10,2)
- tax_amount: decimal(10,2)
- discount_amount: decimal(10,2)
- subtotal: decimal(10,2)
- total: decimal(10,2)
- meta: json (for variant details, etc.)
- created_at: timestamp
- updated_at: timestamp
```

#### `order_status_history` Table
```php
- id: bigint (PK)
- order_id: bigint (FK to orders)
- status: string
- comment: text
- user_id: bigint (FK to users, nullable)
- notified: boolean (customer notification sent)
- created_at: timestamp
```

#### `addresses` Table
```php
- id: bigint (PK)
- addressable_id: bigint (polymorphic)
- addressable_type: string (polymorphic)
- type: enum (billing, shipping)
- first_name: string
- last_name: string
- company: string (nullable)
- address_line_1: string
- address_line_2: string (nullable)
- city: string
- state: string
- postal_code: string
- country: string(2)
- phone: string
- is_default: boolean
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp
```

### API Endpoints

```
GET    /api/v1/orders                    # List orders (paginated, filtered)
POST   /api/v1/orders                    # Create order
GET    /api/v1/orders/{id}               # Get order details
PUT    /api/v1/orders/{id}               # Update order
DELETE /api/v1/orders/{id}               # Cancel order (soft delete)

GET    /api/v1/orders/{id}/items         # Get order items
POST   /api/v1/orders/{id}/items         # Add item to order
PUT    /api/v1/orders/{id}/items/{item}  # Update order item
DELETE /api/v1/orders/{id}/items/{item}  # Remove order item

POST   /api/v1/orders/{id}/status        # Update order status
GET    /api/v1/orders/{id}/history       # Get order status history

POST   /api/v1/orders/{id}/cancel        # Cancel order
POST   /api/v1/orders/{id}/refund        # Refund order

# Customer-specific endpoints
GET    /api/v1/customers/{id}/orders     # Customer order history

# Filtering & Search
GET    /api/v1/orders?status=processing
GET    /api/v1/orders?customer_id=5
GET    /api/v1/orders?order_number=ORD-12345
GET    /api/v1/orders?date_from=2025-01-01&date_to=2025-12-31
```

### Services

```php
OrderService:
- createOrder(array $data)
- updateOrder(Order $order, array $data)
- cancelOrder(Order $order, string $reason)
- updateOrderStatus(Order $order, string $status, string $comment)
- calculateOrderTotals(array $items, array $discounts)
- generateOrderNumber()

OrderItemService:
- addItemToOrder(Order $order, array $itemData)
- updateOrderItem(OrderItem $item, array $data)
- removeOrderItem(OrderItem $item)
- snapshotProductData(Product $product)
```

### Events

```php
// Order lifecycle events
Event::dispatch('order.creating', [$orderData]);
Event::dispatch('order.created', [$order]);
Event::dispatch('order.updating', [$order, $data]);
Event::dispatch('order.updated', [$order]);
Event::dispatch('order.status.changed', [$order, $oldStatus, $newStatus]);
Event::dispatch('order.cancelled', [$order, $reason]);
Event::dispatch('order.completed', [$order]);
Event::dispatch('order.refunded', [$order, $amount]);

// Payment events
Event::dispatch('order.payment.received', [$order, $payment]);
Event::dispatch('order.payment.failed', [$order, $error]);

// Fulfillment events
Event::dispatch('order.shipped', [$order, $trackingInfo]);
Event::dispatch('order.delivered', [$order]);
```

### Query Scopes

```php
Order::scopeByStatus($status)
Order::scopeByCustomer($customerId)
Order::scopeByDateRange($from, $to)
Order::scopePending()
Order::scopeProcessing()
Order::scopeCompleted()
Order::scopeByPaymentStatus($status)
Order::scopeRecentOrders($days = 30)
```

---

## 📊 Stock/Inventory Management

### Overview
Real-time inventory tracking with support for multiple warehouses, stock alerts, and automatic stock updates during order processing.

### Core Features

#### 1. Inventory Tracking
- Real-time stock levels
- Multi-warehouse support
- Stock reservations (during checkout)
- Low stock alerts
- Out of stock handling
- Backorder management

#### 2. Stock Movements
- Stock adjustments (manual)
- Stock history/audit trail
- Reasons for adjustments
- Automatic updates on order events

#### 3. Warehouse Management
- Multiple warehouse locations
- Stock allocation per warehouse
- Transfer between warehouses
- Warehouse priority for fulfillment

### Database Schema

#### `stock_items` Table
```php
- id: bigint (PK)
- product_id: bigint (FK to products)
- warehouse_id: bigint (FK to warehouses, nullable)
- quantity: integer default 0
- reserved_quantity: integer default 0
- available_quantity: integer (computed: quantity - reserved_quantity)
- reorder_point: integer default 10
- reorder_quantity: integer default 50
- is_tracked: boolean default true
- created_at: timestamp
- updated_at: timestamp
```

#### `stock_movements` Table
```php
- id: bigint (PK)
- stock_item_id: bigint (FK to stock_items)
- reference_type: string (order, adjustment, transfer)
- reference_id: bigint (polymorphic)
- type: enum (in, out, reserved, released)
- quantity: integer
- before_quantity: integer
- after_quantity: integer
- reason: string
- notes: text
- user_id: bigint (FK to users, nullable)
- created_at: timestamp
```

#### `warehouses` Table
```php
- id: bigint (PK)
- name: string
- code: string(10) unique
- email: string
- phone: string
- address_line_1: string
- address_line_2: string
- city: string
- state: string
- postal_code: string
- country: string(2)
- is_active: boolean default true
- priority: integer default 0
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp
```

#### `stock_reservations` Table
```php
- id: bigint (PK)
- stock_item_id: bigint (FK to stock_items)
- order_id: bigint (FK to orders, nullable)
- cart_id: string (nullable)
- quantity: integer
- expires_at: timestamp
- released_at: timestamp (nullable)
- created_at: timestamp
- updated_at: timestamp
```

### API Endpoints

```
GET    /api/v1/stock                     # List stock items
GET    /api/v1/stock/product/{id}        # Get product stock
POST   /api/v1/stock/adjust              # Adjust stock levels
POST   /api/v1/stock/reserve             # Reserve stock
POST   /api/v1/stock/release             # Release reserved stock

GET    /api/v1/stock/movements           # Stock movement history
GET    /api/v1/stock/low-stock           # Low stock items

GET    /api/v1/warehouses                # List warehouses
POST   /api/v1/warehouses                # Create warehouse
GET    /api/v1/warehouses/{id}           # Get warehouse
PUT    /api/v1/warehouses/{id}           # Update warehouse
DELETE /api/v1/warehouses/{id}           # Delete warehouse

GET    /api/v1/warehouses/{id}/stock     # Warehouse stock levels
POST   /api/v1/stock/transfer            # Transfer between warehouses
```

### Services

```php
StockService:
- getAvailableStock(Product $product, ?Warehouse $warehouse)
- adjustStock(StockItem $item, int $quantity, string $reason)
- reserveStock(Product $product, int $quantity, $reference)
- releaseReservation(StockReservation $reservation)
- checkLowStock()
- transferStock(StockItem $from, StockItem $to, int $quantity)

StockMovementService:
- recordMovement(StockItem $item, string $type, int $quantity, array $meta)
- getMovementHistory(StockItem $item)
- reconcileStock(StockItem $item)
```

### Events

```php
Event::dispatch('stock.adjusted', [$stockItem, $quantity, $reason]);
Event::dispatch('stock.reserved', [$reservation]);
Event::dispatch('stock.released', [$reservation]);
Event::dispatch('stock.low', [$stockItem]);
Event::dispatch('stock.out', [$product]);
Event::dispatch('stock.transferred', [$fromWarehouse, $toWarehouse, $quantity]);
Event::dispatch('stock.movement.created', [$movement]);
```

### Integration with Order Module

When orders are placed or updated:
```php
// On order creation
1. Reserve stock for order items
2. Update available_quantity

// On order payment
1. Confirm reservation
2. Deduct from actual quantity

// On order cancellation
1. Release reservation
2. Return stock to available

// On order refund
1. Add stock back
2. Create stock movement record
```

---

## 👤 Customer Management

### Overview
Customer account management, authentication, profile handling, and customer-specific features.

### Core Features

#### 1. Customer Accounts
- Customer registration
- Profile management
- Address book (multiple addresses)
- Order history
- Wishlist
- Recently viewed products

#### 2. Authentication
- Login/logout
- Password reset
- Email verification
- Remember me functionality
- API token management

#### 3. Customer Groups
- Customer segmentation
- Group-based pricing
- Special discounts per group

### Database Schema

#### `customers` Table
```php
- id: bigint (PK)
- user_id: bigint (FK to users, nullable)
- customer_number: string(50) unique
- first_name: string
- last_name: string
- email: string unique
- phone: string
- date_of_birth: date
- customer_group_id: bigint (FK to customer_groups, nullable)
- tax_exempt: boolean default false
- notes: text (admin notes)
- is_active: boolean default true
- email_verified_at: timestamp
- last_login_at: timestamp
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp
```

#### `customer_groups` Table
```php
- id: bigint (PK)
- name: string
- slug: string unique
- description: text
- discount_percentage: decimal(5,2) default 0
- is_active: boolean default true
- created_at: timestamp
- updated_at: timestamp
```

### API Endpoints

```
POST   /api/v1/auth/register             # Customer registration
POST   /api/v1/auth/login                # Customer login
POST   /api/v1/auth/logout               # Logout
POST   /api/v1/auth/password/reset       # Request password reset
POST   /api/v1/auth/password/update      # Update password

GET    /api/v1/customers/me              # Current customer profile
PUT    /api/v1/customers/me              # Update profile
GET    /api/v1/customers/me/addresses    # Customer addresses
POST   /api/v1/customers/me/addresses    # Add address
PUT    /api/v1/customers/me/addresses/{id}  # Update address
DELETE /api/v1/customers/me/addresses/{id}  # Delete address

# Admin endpoints
GET    /api/v1/customers                 # List customers
POST   /api/v1/customers                 # Create customer
GET    /api/v1/customers/{id}            # Get customer
PUT    /api/v1/customers/{id}            # Update customer
DELETE /api/v1/customers/{id}            # Delete customer

GET    /api/v1/customer-groups           # List customer groups
POST   /api/v1/customer-groups           # Create group
PUT    /api/v1/customer-groups/{id}      # Update group
DELETE /api/v1/customer-groups/{id}      # Delete group
```

---

## 🛍️ Cart & Checkout

### Overview
Shopping cart management and streamlined checkout process with support for guest checkout and saved carts.

### Core Features

#### 1. Shopping Cart
- Add/remove/update items
- Cart persistence (for logged-in users)
- Guest cart (session-based)
- Cart totals calculation
- Merge carts on login
- Abandoned cart tracking

#### 2. Checkout Process
- Multi-step checkout
- Guest checkout
- Billing/shipping address
- Shipping method selection
- Payment method selection
- Order review
- Order confirmation

### Database Schema

#### `carts` Table
```php
- id: bigint (PK)
- cart_token: string unique (for guest carts)
- customer_id: bigint (FK to customers, nullable)
- session_id: string
- subtotal: decimal(10,2) default 0
- tax_amount: decimal(10,2) default 0
- discount_amount: decimal(10,2) default 0
- total: decimal(10,2) default 0
- coupon_code: string (nullable)
- expires_at: timestamp
- converted_to_order_at: timestamp (nullable)
- created_at: timestamp
- updated_at: timestamp
```

#### `cart_items` Table
```php
- id: bigint (PK)
- cart_id: bigint (FK to carts)
- product_id: bigint (FK to products)
- quantity: integer
- unit_price: decimal(10,2)
- subtotal: decimal(10,2)
- tax_amount: decimal(10,2)
- discount_amount: decimal(10,2)
- total: decimal(10,2)
- meta: json (for product variants, custom options)
- created_at: timestamp
- updated_at: timestamp
```

### API Endpoints

```
GET    /api/v1/cart                      # Get current cart
POST   /api/v1/cart/items                # Add item to cart
PUT    /api/v1/cart/items/{id}           # Update cart item quantity
DELETE /api/v1/cart/items/{id}           # Remove item from cart
DELETE /api/v1/cart                      # Clear cart
POST   /api/v1/cart/coupon               # Apply coupon code
DELETE /api/v1/cart/coupon               # Remove coupon

POST   /api/v1/checkout/address          # Set checkout address
POST   /api/v1/checkout/shipping         # Select shipping method
POST   /api/v1/checkout/payment          # Select payment method
POST   /api/v1/checkout/confirm          # Confirm and create order
GET    /api/v1/checkout/summary          # Get checkout summary
```

---

## 💳 Payment Gateway Integration

### Overview
Support for multiple payment gateways with a unified interface for payment processing.

### Core Features

#### 1. Payment Methods
- Credit/Debit cards
- PayPal
- Stripe
- Bank transfer
- Cash on delivery
- Wallet/Store credit

#### 2. Payment Processing
- Payment authorization
- Payment capture
- Refund processing
- Partial refunds
- Payment webhooks

### Database Schema

#### `payments` Table
```php
- id: bigint (PK)
- order_id: bigint (FK to orders)
- payment_method: string
- transaction_id: string unique
- amount: decimal(10,2)
- currency: string(3)
- status: enum (pending, authorized, completed, failed, refunded)
- gateway_response: json
- refund_amount: decimal(10,2) default 0
- refunded_at: timestamp
- processed_at: timestamp
- created_at: timestamp
- updated_at: timestamp
```

#### `payment_methods` Table
```php
- id: bigint (PK)
- name: string
- slug: string unique
- gateway: string (stripe, paypal, etc.)
- is_active: boolean default true
- configuration: json (encrypted)
- sort_order: integer
- created_at: timestamp
- updated_at: timestamp
```

### API Endpoints

```
GET    /api/v1/payment-methods           # Available payment methods
POST   /api/v1/payments/process          # Process payment
POST   /api/v1/payments/{id}/refund      # Refund payment
GET    /api/v1/payments/{id}/status      # Check payment status

# Webhook endpoints
POST   /api/v1/webhooks/stripe
POST   /api/v1/webhooks/paypal
```

---

## 🚚 Shipping & Fulfillment

### Overview
Shipping calculation, method selection, and fulfillment tracking.

### Core Features

#### 1. Shipping Methods
- Flat rate
- Free shipping (conditional)
- Weight-based rates
- Zone-based rates
- Real-time carrier rates (optional)

#### 2. Fulfillment
- Pick lists generation
- Packing slips
- Shipping label generation
- Tracking number management
- Shipment notifications

### Database Schema

#### `shipping_methods` Table
```php
- id: bigint (PK)
- name: string
- slug: string unique
- description: text
- type: enum (flat_rate, free, weight_based, zone_based, real_time)
- base_cost: decimal(10,2)
- per_item_cost: decimal(10,2)
- calculation_rules: json
- min_order_amount: decimal(10,2) (for free shipping)
- is_active: boolean default true
- sort_order: integer
- created_at: timestamp
- updated_at: timestamp
```

#### `shipments` Table
```php
- id: bigint (PK)
- order_id: bigint (FK to orders)
- tracking_number: string
- carrier: string
- shipped_at: timestamp
- estimated_delivery: timestamp
- delivered_at: timestamp
- notes: text
- created_at: timestamp
- updated_at: timestamp
```

### API Endpoints

```
GET    /api/v1/shipping-methods          # Available shipping methods
POST   /api/v1/shipping/calculate        # Calculate shipping costs
POST   /api/v1/shipments                 # Create shipment
PUT    /api/v1/shipments/{id}            # Update shipment
GET    /api/v1/shipments/{id}/track      # Track shipment
```

---

## 🎟️ Discount & Coupon System

### Overview
Flexible discount rules and coupon codes with various conditions and restrictions.

### Core Features

#### 1. Coupon Types
- Percentage discounts
- Fixed amount discounts
- Free shipping
- Buy X get Y
- Minimum purchase amount

#### 2. Restrictions
- Usage limits (per coupon, per customer)
- Date range (valid from/to)
- Customer groups
- Product/category restrictions
- Minimum order amount

### Database Schema

#### `coupons` Table
```php
- id: bigint (PK)
- code: string unique
- description: text
- type: enum (percentage, fixed_amount, free_shipping, buy_x_get_y)
- discount_value: decimal(10,2)
- max_discount: decimal(10,2) (nullable)
- min_order_amount: decimal(10,2) default 0
- usage_limit: integer (nullable)
- usage_limit_per_customer: integer (nullable)
- times_used: integer default 0
- valid_from: timestamp
- valid_to: timestamp
- is_active: boolean default true
- applies_to: enum (all, products, categories)
- applicable_ids: json (product/category IDs)
- customer_group_ids: json
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp
```

#### `coupon_usages` Table
```php
- id: bigint (PK)
- coupon_id: bigint (FK to coupons)
- customer_id: bigint (FK to customers, nullable)
- order_id: bigint (FK to orders)
- discount_amount: decimal(10,2)
- created_at: timestamp
```

### API Endpoints

```
GET    /api/v1/coupons                   # List coupons (admin)
POST   /api/v1/coupons                   # Create coupon
GET    /api/v1/coupons/{id}              # Get coupon
PUT    /api/v1/coupons/{id}              # Update coupon
DELETE /api/v1/coupons/{id}              # Delete coupon

POST   /api/v1/coupons/validate          # Validate coupon code
GET    /api/v1/coupons/{code}/check      # Check coupon availability
```

---

## ⭐ Review & Rating System

### Overview
Product reviews and ratings with moderation capabilities.

### Core Features

#### 1. Reviews
- Write product reviews
- Star ratings (1-5)
- Review moderation
- Review replies
- Verified purchase badge

#### 2. Helpful Votes
- Mark reviews as helpful
- Sort by helpfulness
- Report inappropriate reviews

### Database Schema

#### `product_reviews` Table
```php
- id: bigint (PK)
- product_id: bigint (FK to products)
- customer_id: bigint (FK to customers)
- order_id: bigint (FK to orders, nullable)
- rating: integer (1-5)
- title: string
- review: text
- is_verified_purchase: boolean default false
- is_approved: boolean default false
- helpful_count: integer default 0
- not_helpful_count: integer default 0
- admin_reply: text (nullable)
- admin_replied_at: timestamp
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp
```

#### `review_votes` Table
```php
- id: bigint (PK)
- review_id: bigint (FK to product_reviews)
- customer_id: bigint (FK to customers)
- vote_type: enum (helpful, not_helpful)
- created_at: timestamp
```

### API Endpoints

```
GET    /api/v1/products/{id}/reviews     # Get product reviews
POST   /api/v1/products/{id}/reviews     # Add review
PUT    /api/v1/reviews/{id}              # Update review
DELETE /api/v1/reviews/{id}              # Delete review

POST   /api/v1/reviews/{id}/vote         # Vote on review
POST   /api/v1/reviews/{id}/report       # Report review

# Admin endpoints
GET    /api/v1/reviews/pending           # Pending reviews
POST   /api/v1/reviews/{id}/approve      # Approve review
POST   /api/v1/reviews/{id}/reply        # Admin reply
```

---

## 💝 Wishlist Feature

### Overview
Save products for later purchase and share wishlists with others.

### Database Schema

#### `wishlists` Table
```php
- id: bigint (PK)
- customer_id: bigint (FK to customers)
- product_id: bigint (FK to products)
- added_at: timestamp
- created_at: timestamp
```

### API Endpoints

```
GET    /api/v1/wishlist                  # Get wishlist
POST   /api/v1/wishlist                  # Add to wishlist
DELETE /api/v1/wishlist/{id}             # Remove from wishlist
POST   /api/v1/wishlist/{id}/cart        # Move to cart
```

---

## 🔍 Search & Filter Enhancement

### Core Features

#### 1. Advanced Search
- Full-text search
- Search suggestions
- Recent searches
- Popular searches

#### 2. Enhanced Filtering
- Price range slider
- Multiple attribute filters
- Filter by availability
- Filter by ratings

### Database Schema

#### `search_logs` Table
```php
- id: bigint (PK)
- customer_id: bigint (FK to customers, nullable)
- query: string
- results_count: integer
- clicked_product_id: bigint (FK to products, nullable)
- ip_address: string
- created_at: timestamp
```

---

## 📈 Analytics & Reporting

### Core Features

#### 1. Sales Analytics
- Revenue reports
- Sales by product
- Sales by category
- Sales trends

#### 2. Customer Analytics
- Customer lifetime value
- Repeat purchase rate
- Customer acquisition
- Customer segmentation

#### 3. Inventory Reports
- Stock levels
- Low stock alerts
- Stock movement history
- Product performance

### API Endpoints

```
GET    /api/v1/analytics/sales           # Sales analytics
GET    /api/v1/analytics/customers       # Customer analytics
GET    /api/v1/analytics/inventory       # Inventory analytics
GET    /api/v1/analytics/top-products    # Best sellers
GET    /api/v1/analytics/abandoned-carts # Abandoned cart report
```

---

## 🗓️ Implementation Roadmap

### Phase 1: Foundation (Weeks 1-3)
**Priority: High**

1. **Customer Management**
   - Customer model and authentication
   - Address management
   - Customer groups
   - Tests

2. **Cart System**
   - Cart models
   - Cart operations (add/remove/update)
   - Cart calculations
   - Tests

### Phase 2: Core Commerce (Weeks 4-7)
**Priority: High**

3. **Order Management**
   - Order models and relationships
   - Order creation from cart
   - Order status management
   - Order history
   - Tests

4. **Stock Management**
   - Stock models
   - Inventory tracking
   - Stock reservations
   - Stock movements
   - Tests

### Phase 3: Payment & Checkout (Weeks 8-10)
**Priority: Medium**

5. **Checkout Process**
   - Multi-step checkout flow
   - Address selection
   - Payment method integration
   - Order confirmation

6. **Payment Gateway**
   - Payment models
   - Gateway abstraction layer
   - Stripe integration
   - PayPal integration
   - Tests

### Phase 4: Fulfillment (Weeks 11-13)
**Priority: Medium**

7. **Shipping Management**
   - Shipping method configuration
   - Shipping calculation
   - Shipment tracking
   - Fulfillment workflow

8. **Warehouse Management**
   - Multiple warehouse support
   - Stock allocation
   - Transfer management

### Phase 5: Marketing Features (Weeks 14-16)
**Priority: Low-Medium**

9. **Discount System**
   - Coupon models
   - Discount rules engine
   - Coupon validation
   - Usage tracking

10. **Review System**
    - Review models
    - Rating aggregation
    - Review moderation
    - Helpful votes

### Phase 6: Enhancement Features (Weeks 17-19)
**Priority: Low**

11. **Wishlist**
    - Wishlist models
    - Wishlist operations
    - Share functionality

12. **Search Enhancement**
    - Search indexing
    - Filter optimization
    - Search analytics

13. **Analytics**
    - Report generation
    - Dashboard metrics
    - Export functionality

### Phase 7: Polish & Documentation (Weeks 20-22)
**Priority: High**

14. **Testing & QA**
    - Comprehensive test coverage
    - Integration tests
    - Performance testing

15. **Documentation**
    - API documentation updates
    - Usage guides
    - Migration guides
    - Code examples

---

## 💾 Database Schema Planning

### Complete ER Diagram Overview

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│  customers  │──<──<─│   orders    │──>──>─│ order_items │
└─────────────┘       └─────────────┘       └─────────────┘
       │                      │                      │
       │                      │                      │
       ▼                      ▼                      ▼
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│  addresses  │       │  payments   │       │  products   │
└─────────────┘       └─────────────┘       └─────────────┘
                                                    │
                                                    │
                                                    ▼
                                            ┌─────────────┐
                                            │stock_items  │
                                            └─────────────┘
```

### Migration Priority

1. **High Priority (Phase 1-2)**
   - customers
   - customer_groups
   - addresses
   - carts
   - cart_items
   - orders
   - order_items
   - order_status_history

2. **Medium Priority (Phase 3-4)**
   - payments
   - payment_methods
   - stock_items
   - stock_movements
   - warehouses
   - stock_reservations
   - shipping_methods
   - shipments

3. **Low Priority (Phase 5-6)**
   - coupons
   - coupon_usages
   - product_reviews
   - review_votes
   - wishlists
   - search_logs

---

## 🔌 API Endpoint Planning

### Endpoint Organization

All endpoints follow the pattern: `/api/v1/{resource}`

### Naming Conventions

- Use plural nouns for resources
- Use kebab-case for multi-word resources
- Use nested routes for relationships
- Use action verbs for special operations

### Response Format

All endpoints use `ResponseProtocol`:
```json
{
  "status": "success|error",
  "message": "Human readable message",
  "data": {} // or "errors": {}
}
```

### Authentication

- Most endpoints require authentication
- Use Laravel Sanctum or Passport
- Token-based authentication
- Middleware: `auth:sanctum`

### Rate Limiting

- Apply rate limiting per customer
- Different limits for public vs authenticated
- Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

---

## 🛠️ Technical Considerations

### Performance Optimization

1. **Database Indexing**
   - Index foreign keys
   - Index frequently queried columns
   - Composite indexes for complex queries

2. **Caching Strategy**
   - Cache product data
   - Cache frequently accessed orders
   - Cache customer sessions
   - Use Redis for cart storage

3. **Query Optimization**
   - Eager load relationships
   - Use query scopes
   - Implement pagination
   - Use database transactions

### Security

1. **Data Protection**
   - Encrypt sensitive data (payment info)
   - Hash passwords with bcrypt
   - Validate all inputs
   - Sanitize outputs

2. **Access Control**
   - Implement role-based permissions
   - Verify customer ownership
   - Secure admin endpoints
   - CSRF protection

3. **Payment Security**
   - PCI DSS compliance
   - Use payment gateway SDKs
   - Never store full card details
   - Implement 3D Secure

### Testing Strategy

1. **Unit Tests**
   - Test models
   - Test services
   - Test helper classes

2. **Feature Tests**
   - Test API endpoints
   - Test workflows
   - Test integrations

3. **Coverage Goals**
   - Aim for 80%+ code coverage
   - 100% coverage for critical paths (checkout, payment)
   - Test edge cases

### Event System Integration

Hook into existing event system:
```php
Event::hook('order.created', function($order) {
    // Send order confirmation email
    // Update stock levels
    // Trigger analytics
});

Event::hook('payment.completed', function($payment) {
    // Update order status
    // Generate invoice
});
```

### Service Provider Registration

Each new module needs a service provider:
```php
// In CoreServiceProvider::register()
$this->app->register(OrderServiceProvider::class);
$this->app->register(StockServiceProvider::class);
$this->app->register(CustomerServiceProvider::class);
$this->app->register(CartServiceProvider::class);
```

### Backward Compatibility

- Don't modify existing Product module APIs
- Add new features as separate modules
- Use database migrations for schema changes
- Version API endpoints (v1, v2)

### Internationalization (i18n)

- Support multiple languages
- Use Laravel localization
- Translatable error messages
- Multi-currency support

### Configuration

Add new config options to `config/arkenstone.php`:
```php
'order' => [
    'number_prefix' => env('ARKENSTONE_ORDER_PREFIX', 'ORD-'),
    'number_length' => env('ARKENSTONE_ORDER_NUMBER_LENGTH', 8),
],

'stock' => [
    'low_stock_threshold' => env('ARKENSTONE_LOW_STOCK_THRESHOLD', 10),
    'enable_reservations' => env('ARKENSTONE_ENABLE_RESERVATIONS', true),
    'reservation_expires_minutes' => env('ARKENSTONE_RESERVATION_EXPIRES', 30),
],

'cart' => [
    'lifetime_minutes' => env('ARKENSTONE_CART_LIFETIME', 43200), // 30 days
    'max_items' => env('ARKENSTONE_CART_MAX_ITEMS', 100),
],
```

---

## 📝 Next Steps

### Immediate Actions

1. **Review & Approval**
   - Stakeholder review of this plan
   - Prioritize features based on business needs
   - Allocate development resources

2. **Detailed Design**
   - Create detailed wireframes for checkout flow
   - Design database schema in detail
   - Plan API contracts

3. **Setup Development Environment**
   - Create feature branches
   - Setup testing framework
   - Configure CI/CD pipeline

4. **Begin Phase 1 Implementation**
   - Start with Customer Management
   - Implement authentication
   - Create base tests

### Documentation Updates Needed

- Update API_DOCUMENTATION.md with new endpoints
- Create CUSTOMER_GUIDE.md for customer-facing features
- Create ADMIN_GUIDE.md for admin features
- Update TESTING.md with new test strategies

### Questions to Answer

1. Which payment gateways are priority?
2. Do we need multi-currency support in v1?
3. What are the shipping requirements (domestic only or international)?
4. Do we need subscription/recurring payment support?
5. What level of inventory management is needed (simple vs advanced)?

---

## 📚 References

### Existing Documentation
- [API Documentation](./API_DOCUMENTATION.md)
- [Testing Guide](./TESTING.md)
- [Local Development Guide](./LOCAL_DEVELOPMENT_GUIDE.md)
- [Seeder Usage](./SEEDER_USAGE.md)

### Laravel Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Laravel Events](https://laravel.com/docs/events)

### E-Commerce Best Practices
- PCI DSS Compliance Guidelines
- GDPR Data Protection
- E-Commerce UX Patterns

---

**Document Status:** Draft v1.0  
**Last Updated:** December 15, 2025  
**Next Review:** After stakeholder feedback
