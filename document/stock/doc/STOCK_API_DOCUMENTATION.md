# Stock Module API Documentation

**Version:** 1.0.0  
**Base URL:** `/api/v1`  
**Last Updated:** December 16, 2025

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Stock Endpoints](#stock-endpoints)
5. [Stock Reservation Endpoints](#stock-reservation-endpoints)
6. [Supplier Endpoints](#supplier-endpoints)
7. [Variant & Variation Option Endpoints](#variant--variation-option-endpoints)
8. [Request Validation Rules](#request-validation-rules)
9. [Resource Schemas](#resource-schemas)
10. [Pagination & Filtering](#pagination--filtering)
11. [Error Handling](#error-handling)
12. [Stock Reservation Workflow](#stock-reservation-workflow)
13. [Database Schema Reference](#database-schema-reference)

---

## Overview

The Stock Module provides comprehensive inventory management capabilities including:

- **Stock Management** - Track product inventory with SKUs, barcodes, quantities, and pricing
- **Multi-Warehouse Support** - Associate stock with suppliers/warehouses
- **Variant System** - Support for product variations (size, color, storage, etc.)
- **Reservation System** - Reserve stock for carts and orders with automatic expiry
- **Low Stock Alerts** - Monitor inventory levels and get low stock notifications
- **Stock Adjustments** - Manually adjust quantities with reason tracking

### Key Concepts

```
Product (from Product Module)
    ↓
Stock Record (SKU, quantity, supplier)
    ↓
Variant Options (size, color, storage)
    ↓
Reservations (cart/order holds)
```

### Quantity Calculations

- **quantity_on_hand** - Physical stock in warehouse
- **quantity_reserved** - Sum of active reservations (pending, checking_out, committed)
- **quantity_available** - Computed: `quantity_on_hand - quantity_reserved`

---

## Authentication

Currently, the Stock Module API does not require authentication. For production use, implement authentication middleware on routes.

---

## Response Format

All responses follow the **ResponseProtocol** format:

### Success Response
```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": { /* response data */ }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Operation failed",
  "errors": {
    "field": ["Error message"]
  }
}
```

### Paginated Response
```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": {
    "data": [ /* array of items */ ],
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 5,
      "per_page": 15,
      "to": 15,
      "total": 68
    },
    "links": {
      "first": "http://api.example.com/api/v1/stocks?page=1",
      "last": "http://api.example.com/api/v1/stocks?page=5",
      "prev": null,
      "next": "http://api.example.com/api/v1/stocks?page=2"
    }
  }
}
```

---

## Stock Endpoints

### 1. List All Stocks

**GET** `/api/v1/stocks`

List all stock records with pagination and filtering.

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Items per page (1-100) |
| `page` | integer | 1 | Page number |
| `product_id` | integer | - | Filter by product ID |
| `supplier_id` | integer | - | Filter by supplier ID |
| `status` | string | - | Filter by status (`active`, `inactive`, `out_of_stock`, `discontinued`) |
| `active` | boolean | - | Filter active stocks only |
| `low_stock` | boolean | - | Filter low stock items |
| `out_of_stock` | boolean | - | Filter out of stock items |
| `in_stock` | boolean | - | Filter in stock items |

**Example Request:**
```bash
GET /api/v1/stocks?per_page=20&supplier_id=5&status=active
```

**Example Response:**
```json
{
  "status": "success",
  "message": "Stocks retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "product_id": 10,
        "sku": "STK-LAPTOP-001-A4B2",
        "barcode": "1234567890123",
        "price": "1299.99",
        "cost": "779.99",
        "weight": "2.500",
        "quantity_on_hand": 150,
        "quantity_reserved": 10,
        "quantity_available": 140,
        "min_stock_level": 20,
        "supplier_id": 5,
        "image_url_id": null,
        "status": "active",
        "is_available": true,
        "is_low_stock": false,
        "product": { /* Product resource */ },
        "supplier": { /* Supplier resource */ },
        "variation_options": [],
        "reservations": [],
        "created_at": "2024-12-16T10:00:00.000000Z",
        "updated_at": "2024-12-16T10:00:00.000000Z"
      }
    ],
    "meta": { /* pagination meta */ },
    "links": { /* pagination links */ }
  }
}
```

---

### 2. Get Single Stock

**GET** `/api/v1/stocks/{id}`

Retrieve a single stock record by ID with all relationships loaded.

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Stock ID |

**Example Request:**
```bash
GET /api/v1/stocks/1
```

**Example Response:**
```json
{
  "status": "success",
  "message": "Stock retrieved successfully",
  "data": {
    "id": 1,
    "product_id": 10,
    "sku": "STK-LAPTOP-001-A4B2",
    "barcode": "1234567890123",
    "price": "1299.99",
    "cost": "779.99",
    "weight": "2.500",
    "quantity_on_hand": 150,
    "quantity_reserved": 10,
    "quantity_available": 140,
    "min_stock_level": 20,
    "supplier_id": 5,
    "image_url_id": 25,
    "status": "active",
    "is_available": true,
    "is_low_stock": false,
    "product": {
      "id": 10,
      "name": "Dell XPS 15 Laptop",
      "slug": "dell-xps-15-laptop",
      "price": "1299.99",
      "is_active": true
    },
    "supplier": {
      "id": 5,
      "name": "Tech Distributors International",
      "supplier_code": "SUP-TECH-001",
      "status": "active"
    },
    "variation_options": [
      {
        "id": 15,
        "name": "256GB",
        "variant": {
          "id": 4,
          "name": "Storage"
        }
      },
      {
        "id": 8,
        "name": "Silver",
        "variant": {
          "id": 2,
          "name": "Color"
        }
      }
    ],
    "reservations": [
      {
        "id": 5,
        "quantity": 2,
        "status": "pending",
        "expires_at": "2024-12-16T10:15:00.000000Z"
      }
    ],
    "created_at": "2024-12-16T10:00:00.000000Z",
    "updated_at": "2024-12-16T10:00:00.000000Z"
  }
}
```

---

### 3. Create Stock

**POST** `/api/v1/stocks`

Create a new stock record.

**Request Body:**

```json
{
  "product_id": 10,
  "sku": "STK-LAPTOP-001-A4B2",
  "barcode": "1234567890123",
  "price": 1299.99,
  "cost": 779.99,
  "weight": 2.5,
  "quantity_on_hand": 150,
  "min_stock_level": 20,
  "supplier_id": 5,
  "image_url_id": 25,
  "status": "active",
  "variation_option_ids": [15, 8]
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | Yes | Must exist in products table |
| `sku` | string | Yes | Max 100 chars, unique |
| `barcode` | string | No | Max 100 chars |
| `price` | decimal | Yes | Min 0 |
| `cost` | decimal | No | Min 0 |
| `weight` | decimal | No | Min 0 |
| `quantity_on_hand` | integer | Yes | Min 0 |
| `min_stock_level` | integer | No | Min 0 |
| `supplier_id` | integer | Yes | Must exist in suppliers table |
| `image_url_id` | integer | No | Must exist in product_images table |
| `status` | string | No | `active`, `inactive` |
| `variation_option_ids` | array | No | Each ID must exist in variation_options table |

**Example Response:**
```json
{
  "status": "success",
  "message": "Stock created successfully",
  "data": { /* Stock resource */ }
}
```

**Error Response (422):**
```json
{
  "message": "The sku has already been taken.",
  "errors": {
    "sku": ["The sku has already been taken."]
  }
}
```

---

### 4. Update Stock

**PUT/PATCH** `/api/v1/stocks/{id}`

Update an existing stock record.

**Request Body:**

```json
{
  "price": 1199.99,
  "quantity_on_hand": 200,
  "min_stock_level": 25,
  "status": "active"
}
```

**Validation Rules:**
Same as Create, but all fields are optional (use `sometimes` instead of `required`).

**Example Response:**
```json
{
  "status": "success",
  "message": "Stock updated successfully",
  "data": { /* Updated Stock resource */ }
}
```

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Stock not found",
  "errors": null
}
```

---

### 5. Delete Stock

**DELETE** `/api/v1/stocks/{id}`

Soft delete a stock record. Fails if there are active reservations.

**Example Response (200):**
```json
{
  "status": "success",
  "message": "Stock deleted successfully",
  "data": null
}
```

**Error Response (500):**
```json
{
  "status": "error",
  "message": "Failed to delete stock",
  "errors": {
    "error": "Cannot delete stock with active reservations. Please release or fulfill them first."
  }
}
```

---

### 6. Search Stocks

**GET** `/api/v1/stocks/search`

Search stocks by SKU, barcode, or product name.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | Yes | Search query |
| `per_page` | integer | No | Items per page (default: 15) |

**Example Request:**
```bash
GET /api/v1/stocks/search?search=LAPTOP
```

**Example Response:**
```json
{
  "status": "success",
  "message": "Search results retrieved successfully",
  "data": {
    "data": [ /* Matching stocks */ ],
    "meta": { /* pagination */ },
    "links": { /* pagination links */ }
  }
}
```

**Error Response (400):**
```json
{
  "status": "error",
  "message": "Search query is required",
  "errors": null
}
```

---

### 7. Get Low Stock Items

**GET** `/api/v1/stocks/low-stock`

Get all active stock items where `quantity_available <= min_stock_level`.

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Items per page |

**Example Response:**
```json
{
  "status": "success",
  "message": "Low stock items retrieved successfully",
  "data": {
    "data": [ /* Low stock items */ ],
    "meta": { /* pagination */ },
    "links": { /* pagination links */ }
  }
}
```

---

### 8. Get Out of Stock Items

**GET** `/api/v1/stocks/out-of-stock`

Get all active stock items where `quantity_available <= 0`.

**Example Response:**
```json
{
  "status": "success",
  "message": "Out of stock items retrieved successfully",
  "data": {
    "data": [ /* Out of stock items */ ],
    "meta": { /* pagination */ },
    "links": { /* pagination links */ }
  }
}
```

---

### 9. Check Stock Availability

**POST** `/api/v1/stocks/check-availability`

Check if sufficient quantity is available for a given stock.

**Request Body:**

```json
{
  "stock_id": 1,
  "quantity": 50
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `stock_id` | integer | Yes | Must exist in stocks table |
| `quantity` | integer | Yes | Min 1 |

**Example Response (Available):**
```json
{
  "status": "success",
  "message": "Stock is available",
  "data": {
    "available": true,
    "quantity_available": 140,
    "stock": { /* Stock resource */ },
    "reason": null
  }
}
```

**Example Response (Not Available):**
```json
{
  "status": "success",
  "message": "Stock is not available",
  "data": {
    "available": false,
    "quantity_available": 10,
    "stock": { /* Stock resource */ },
    "reason": "Insufficient quantity"
  }
}
```

---

### 10. Adjust Stock Quantity

**POST** `/api/v1/stocks/{id}/adjust-quantity`

Manually adjust stock quantity (increase or decrease) with reason tracking.

**Request Body:**

```json
{
  "quantity": -5,
  "reason": "Damaged items removed from inventory"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `quantity` | integer | Yes | Can be positive or negative |
| `reason` | string | No | Max 255 chars |

**Example Response:**
```json
{
  "status": "success",
  "message": "Stock quantity adjusted successfully",
  "data": { /* Updated Stock resource */ }
}
```

---

## Stock Reservation Endpoints

### 1. Reserve Stock

**POST** `/api/v1/stock-reservations/reserve`

Reserve stock for a cart or order. Automatically sets expiry time (default 15 minutes for carts).

**Request Body:**

```json
{
  "stock_id": 1,
  "quantity": 2,
  "reference_type": "cart",
  "reference_id": 12345
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `stock_id` | integer | Yes | Must exist in stocks table |
| `quantity` | integer | Yes | Min 1 |
| `reference_type` | string | Yes | `cart`, `order` |
| `reference_id` | integer | Yes | ID of cart/order |

**Example Response (201):**
```json
{
  "status": "success",
  "message": "Stock reserved successfully",
  "data": {
    "id": 10,
    "stock_id": 1,
    "quantity": 2,
    "status": "pending",
    "reference_type": "cart",
    "reference_id": 12345,
    "expires_at": "2024-12-16T10:15:00.000000Z",
    "notes": null,
    "is_expired": false,
    "is_pending": true,
    "is_committed": false,
    "stock": { /* Stock resource */ },
    "created_at": "2024-12-16T10:00:00.000000Z",
    "updated_at": "2024-12-16T10:00:00.000000Z"
  }
}
```

**Error Response (422):**
```json
{
  "status": "error",
  "message": "Failed to reserve stock",
  "errors": {
    "error": "Insufficient stock available. Requested: 20, Available: 10"
  }
}
```

---

### 2. Get Reservation

**GET** `/api/v1/stock-reservations/{id}`

Retrieve a single reservation by ID.

**Example Response:**
```json
{
  "status": "success",
  "message": "Reservation retrieved successfully",
  "data": { /* StockReservation resource */ }
}
```

---

### 3. Update Reservation Status

**POST** `/api/v1/stock-reservations/{id}/update-status`

Manually update reservation status.

**Request Body:**

```json
{
  "status": "expired"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `status` | string | Yes | `pending`, `checking_out`, `committed`, `fulfilled`, `cancelled`, `expired` |

**Example Response:**
```json
{
  "status": "success",
  "message": "Reservation status updated successfully",
  "data": { /* Updated reservation */ }
}
```

---

### 4. Extend Reservation Expiry

**POST** `/api/v1/stock-reservations/{id}/extend`

Extend the expiry time of a reservation.

**Request Body:**

```json
{
  "minutes": 30
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `minutes` | integer | Yes | Min 1, Max 60 |

**Example Response:**
```json
{
  "status": "success",
  "message": "Reservation extended successfully",
  "data": { /* Updated reservation */ }
}
```

---

### 5. Release Reservation

**POST** `/api/v1/stock-reservations/{id}/release`

Cancel a reservation and return quantity to available stock.

**Example Response:**
```json
{
  "status": "success",
  "message": "Reservation released successfully",
  "data": null
}
```

**Effects:**
- Sets reservation status to `release`
- Decrements `stock.quantity_reserved` by reservation quantity
- Makes quantity available again

---

### 6. Commit Reservation

**POST** `/api/v1/stock-reservations/{id}/commit`

Commit a reservation when order is placed and payment is confirmed.

**Example Response:**
```json
{
  "status": "success",
  "message": "Reservation committed successfully",
  "data": { /* Updated reservation with status=committed */ }
}
```

**Effects:**
- Changes status from `pending` to `committed`
- Extends expiry to 3 days
- Keeps `quantity_reserved` unchanged

---

### 7. Fulfill Reservation

**POST** `/api/v1/stock-reservations/{id}/fulfill`

Fulfill a reservation when order is shipped (deducts from physical stock).

**Example Response:**
```json
{
  "status": "success",
  "message": "Reservation fulfilled successfully",
  "data": { /* Updated reservation with status=fulfilled */ }
}
```

**Effects:**
- Changes status to `fulfilled`
- Decrements `stock.quantity_on_hand` by reservation quantity
- Decrements `stock.quantity_reserved` by reservation quantity
- Sets `expires_at` to null

---

### 8. Get Active Reservations by Stock

**GET** `/api/v1/stock-reservations/stock/{stockId}/active`

Get all active reservations for a specific stock.

**Example Response:**
```json
{
  "status": "success",
  "message": "Active reservations retrieved successfully",
  "data": [ /* Array of active reservations */ ]
}
```

---

### 9. Get Reservations by Reference

**GET** `/api/v1/stock-reservations/by-reference`

Get all reservations for a specific cart or order.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `reference_type` | string | Yes | `cart`, `order` |
| `reference_id` | integer | Yes | Cart/Order ID |

**Example Request:**
```bash
GET /api/v1/stock-reservations/by-reference?reference_type=cart&reference_id=12345
```

**Example Response:**
```json
{
  "status": "success",
  "message": "Reservations retrieved successfully",
  "data": [ /* Array of reservations */ ]
}
```

---

## Supplier Endpoints

### 1. List Suppliers

**GET** `/api/v1/suppliers`

List all suppliers with pagination and filtering.

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Items per page |
| `status` | string | - | Filter by status |
| `active` | boolean | - | Filter active only |

**Example Response:**
```json
{
  "status": "success",
  "message": "Suppliers retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Tech Distributors International",
        "contact_person": "Michael Chen",
        "email": "orders@techdist.com",
        "phone": "+1-555-0101",
        "address": "1500 Technology Drive",
        "city": "San Jose",
        "state": "California",
        "country": "USA",
        "postal_code": "95110",
        "supplier_code": "SUP-TECH-001",
        "status": "active",
        "notes": "Primary supplier for electronics",
        "is_active": true,
        "stocks_count": 50,
        "created_at": "2024-12-16T10:00:00.000000Z",
        "updated_at": "2024-12-16T10:00:00.000000Z"
      }
    ],
    "meta": { /* pagination */ },
    "links": { /* pagination links */ }
  }
}
```

---

### 2. Get Single Supplier

**GET** `/api/v1/suppliers/{id}`

Retrieve a single supplier by ID.

---

### 3. Create Supplier

**POST** `/api/v1/suppliers`

Create a new supplier.

**Request Body:**

```json
{
  "name": "New Tech Supplier",
  "contact_person": "John Doe",
  "email": "john@newtech.com",
  "phone": "+1-555-9999",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "postal_code": "10001",
  "supplier_code": "SUP-NEW-001",
  "status": "active",
  "notes": "New supplier for testing"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | Max 255 chars |
| `contact_person` | string | No | Max 255 chars |
| `email` | string | No | Valid email, max 255 chars |
| `phone` | string | No | Max 50 chars |
| `address` | string | No | - |
| `city` | string | No | Max 100 chars |
| `state` | string | No | Max 100 chars |
| `country` | string | No | Max 100 chars |
| `postal_code` | string | No | Max 20 chars |
| `supplier_code` | string | Yes | Max 50 chars, unique |
| `status` | string | No | `active`, `inactive` |
| `notes` | string | No | - |

---

### 4. Update Supplier

**PUT/PATCH** `/api/v1/suppliers/{id}`

Update an existing supplier.

---

### 5. Delete Supplier

**DELETE** `/api/v1/suppliers/{id}`

Soft delete a supplier. Fails if supplier has active stocks.

**Error Response (500):**
```json
{
  "status": "error",
  "message": "Failed to delete supplier",
  "errors": {
    "error": "Cannot delete supplier with existing stocks. Please reassign or delete 50 stock items first."
  }
}
```

---

### 6. Search Suppliers

**GET** `/api/v1/suppliers/search`

Search suppliers by name, supplier code, or email.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | Yes | Search query |
| `per_page` | integer | No | Items per page |

---

## Variant & Variation Option Endpoints

### 1. List Variants

**GET** `/api/v1/variants`

List all product variants (Size, Color, Storage, etc.).

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Items per page |
| `search` | string | - | Search by name |

**Example Response:**
```json
{
  "status": "success",
  "message": "Variants retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Size",
        "variation_options": [
          {
            "id": 1,
            "name": "Small (S)",
            "meta": {"code": "S", "sort": 1}
          },
          {
            "id": 2,
            "name": "Medium (M)",
            "meta": {"code": "M", "sort": 2}
          }
        ],
        "options_count": 6,
        "created_at": "2024-12-16T10:00:00.000000Z",
        "updated_at": "2024-12-16T10:00:00.000000Z"
      }
    ],
    "meta": { /* pagination */ },
    "links": { /* pagination links */ }
  }
}
```

---

### 2. Get Single Variant

**GET** `/api/v1/variants/{id}`

Retrieve a single variant with its options.

---

### 3. Create Variant

**POST** `/api/v1/variants`

Create a new variant type.

**Request Body:**

```json
{
  "name": "Capacity"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | Max 255 chars |

---

### 4. Update Variant

**PUT/PATCH** `/api/v1/variants/{id}`

Update a variant name.

---

### 5. Delete Variant

**DELETE** `/api/v1/variants/{id}`

Delete a variant. Fails if variant has associated options.

---

### 6. Get Options by Variant

**GET** `/api/v1/variants/{variantId}/options`

Get all variation options for a specific variant.

**Example Response:**
```json
{
  "status": "success",
  "message": "Variation options retrieved successfully",
  "data": [
    {
      "id": 1,
      "variant_id": 1,
      "name": "Small (S)",
      "meta": {"code": "S", "sort": 1},
      "variant": {
        "id": 1,
        "name": "Size"
      },
      "stocks_count": 25,
      "created_at": "2024-12-16T10:00:00.000000Z",
      "updated_at": "2024-12-16T10:00:00.000000Z"
    }
  ]
}
```

---

### 7. Get Single Variation Option

**GET** `/api/v1/variation-options/{id}`

Retrieve a single variation option.

---

### 8. Create Variation Option

**POST** `/api/v1/variation-options`

Create a new variation option.

**Request Body:**

```json
{
  "variant_id": 2,
  "name": "Navy Blue",
  "meta": {
    "hex": "#000080",
    "rgb": [0, 0, 128]
  }
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `variant_id` | integer | Yes | Must exist in variants table |
| `name` | string | Yes | Max 255 chars |
| `meta` | array/json | No | Additional metadata |

---

### 9. Update Variation Option

**PUT/PATCH** `/api/v1/variation-options/{id}`

Update a variation option.

---

### 10. Delete Variation Option

**DELETE** `/api/v1/variation-options/{id}`

Delete a variation option. Fails if option is used in stocks.

---

## Request Validation Rules

### Stock Creation/Update

```php
'product_id' => 'required|integer|exists:products,id'
'sku' => 'required|string|max:100|unique:stocks,sku'
'barcode' => 'nullable|string|max:100'
'price' => 'required|numeric|min:0'
'cost' => 'nullable|numeric|min:0'
'weight' => 'nullable|numeric|min:0'
'quantity_on_hand' => 'required|integer|min:0'
'quantity_reserved' => // Computed, not accepted in requests
'min_stock_level' => 'nullable|integer|min:0'
'supplier_id' => 'required|integer|exists:suppliers,id'
'image_url_id' => 'nullable|integer|exists:product_images,id'
'status' => 'nullable|string|in:active,inactive'
'variation_option_ids' => 'nullable|array'
'variation_option_ids.*' => 'integer|exists:variation_options,id'
```

### Reservation Creation

```php
'stock_id' => 'required|integer|exists:stocks,id'
'quantity' => 'required|integer|min:1'
'reference_type' => 'required|string|in:cart,order'
'reference_id' => 'required|integer'
```

### Supplier Creation/Update

```php
'name' => 'required|string|max:255'
'contact_person' => 'nullable|string|max:255'
'email' => 'nullable|email|max:255'
'phone' => 'nullable|string|max:50'
'address' => 'nullable|string'
'city' => 'nullable|string|max:100'
'state' => 'nullable|string|max:100'
'country' => 'nullable|string|max:100'
'postal_code' => 'nullable|string|max:20'
'supplier_code' => 'required|string|max:50|unique:suppliers,supplier_code'
'status' => 'nullable|string|in:active,inactive'
'notes' => 'nullable|string'
```

---

## Resource Schemas

### Stock Resource

```json
{
  "id": 1,
  "product_id": 10,
  "sku": "STK-LAPTOP-001-A4B2",
  "barcode": "1234567890123",
  "price": "1299.99",
  "cost": "779.99",
  "weight": "2.500",
  "quantity_on_hand": 150,
  "quantity_reserved": 10,
  "quantity_available": 140,
  "min_stock_level": 20,
  "supplier_id": 5,
  "image_url_id": 25,
  "status": "active",
  "is_available": true,
  "is_low_stock": false,
  "product": { /* ProductResource */ },
  "supplier": { /* SupplierResource */ },
  "variation_options": [ /* VariationOptionResource[] */ ],
  "image": { /* ProductImageResource */ },
  "reservations": [ /* StockReservationResource[] */ ],
  "created_at": "2024-12-16T10:00:00.000000Z",
  "updated_at": "2024-12-16T10:00:00.000000Z"
}
```

### StockReservation Resource

```json
{
  "id": 1,
  "stock_id": 5,
  "quantity": 2,
  "status": "pending",
  "reference_type": "cart",
  "reference_id": 123,
  "expires_at": "2024-12-16T10:15:00.000000Z",
  "notes": null,
  "is_expired": false,
  "is_pending": true,
  "is_committed": false,
  "stock": { /* StockResource */ },
  "created_at": "2024-12-16T10:00:00.000000Z",
  "updated_at": "2024-12-16T10:00:00.000000Z"
}
```

### Supplier Resource

```json
{
  "id": 1,
  "name": "Tech Distributors International",
  "contact_person": "Michael Chen",
  "email": "orders@techdist.com",
  "phone": "+1-555-0101",
  "address": "1500 Technology Drive",
  "city": "San Jose",
  "state": "California",
  "country": "USA",
  "postal_code": "95110",
  "supplier_code": "SUP-TECH-001",
  "status": "active",
  "notes": "Primary supplier for electronics",
  "is_active": true,
  "stocks_count": 50,
  "created_at": "2024-12-16T10:00:00.000000Z",
  "updated_at": "2024-12-16T10:00:00.000000Z"
}
```

### Variant Resource

```json
{
  "id": 1,
  "name": "Size",
  "variation_options": [ /* VariationOptionResource[] */ ],
  "options_count": 6,
  "created_at": "2024-12-16T10:00:00.000000Z",
  "updated_at": "2024-12-16T10:00:00.000000Z"
}
```

### VariationOption Resource

```json
{
  "id": 1,
  "variant_id": 1,
  "name": "Small (S)",
  "meta": {"code": "S", "sort": 1},
  "variant": { /* VariantResource */ },
  "stocks_count": 25,
  "created_at": "2024-12-16T10:00:00.000000Z",
  "updated_at": "2024-12-16T10:00:00.000000Z"
}
```

---

## Pagination & Filtering

### Pagination Parameters

All list endpoints support pagination:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Items per page (max 100) |
| `page` | integer | 1 | Current page number |

### Filter Parameters

**Stock Endpoints:**
- `product_id` - Filter by product
- `supplier_id` - Filter by supplier
- `status` - Filter by status
- `active` - Boolean, active stocks only
- `low_stock` - Boolean, low stock items
- `out_of_stock` - Boolean, out of stock items
- `in_stock` - Boolean, in stock items

**Supplier Endpoints:**
- `status` - Filter by status
- `active` - Boolean, active suppliers only

**Variant Endpoints:**
- `search` - Search by variant name

---

## Error Handling

### HTTP Status Codes

| Code | Meaning | When Used |
|------|---------|-----------|
| 200 | OK | Successful request |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request parameters |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server error |

### Error Response Format

```json
{
  "status": "error",
  "message": "User-friendly error message",
  "errors": {
    "field_name": [
      "Specific validation error"
    ]
  }
}
```

### Common Errors

**Validation Error (422):**
```json
{
  "message": "The sku has already been taken.",
  "errors": {
    "sku": ["The sku has already been taken."],
    "supplier_id": ["The selected supplier id is invalid."]
  }
}
```

**Not Found Error (404):**
```json
{
  "status": "error",
  "message": "Stock not found",
  "errors": null
}
```

**Business Logic Error (422):**
```json
{
  "status": "error",
  "message": "Failed to reserve stock",
  "errors": {
    "error": "Insufficient stock available. Requested: 20, Available: 10"
  }
}
```

---

## Stock Reservation Workflow

### Reservation Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│                    RESERVATION LIFECYCLE                     │
└─────────────────────────────────────────────────────────────┘

1. RESERVE (pending)
   └─ User adds item to cart
   └─ quantity_reserved += quantity
   └─ expires_at = now + 15 minutes
   
2. EXTEND (pending)
   └─ User updates cart
   └─ expires_at += additional minutes
   
3. COMMIT (committed)
   └─ User places order
   └─ Payment confirmed
   └─ expires_at = now + 3 days
   └─ quantity_reserved remains
   
4. FULFILL (fulfilled)
   └─ Order shipped
   └─ quantity_on_hand -= quantity
   └─ quantity_reserved -= quantity
   └─ expires_at = null
   
Alternative: RELEASE/CANCEL
   └─ Cart abandoned or order cancelled
   └─ quantity_reserved -= quantity
   └─ Stock returns to available pool
```

### Status Transitions

```
pending → checking_out → committed → fulfilled
   ↓           ↓            ↓
cancelled   cancelled   cancelled
   ↓           ↓            ↓
expired    expired      expired
```

### Auto-Expiry Behavior

Reservations automatically expire based on status:
- **pending** - Expires in 15 minutes (cart reservations)
- **checking_out** - Expires in 30 minutes (checkout process)
- **committed** - Expires in 3 days (order placed)
- **fulfilled** - No expiry (order completed)

**Background Job:** Run a scheduled job to call `/release-expired` endpoint periodically.

---

## Database Schema Reference

### Tables Overview

| Table | Purpose | Soft Deletes |
|-------|---------|--------------|
| `suppliers` | Supplier/warehouse information | Yes |
| `variants` | Variant types (Size, Color, etc.) | No |
| `variation_options` | Specific options (Small, Red, 128GB) | No |
| `stocks` | Stock records with quantities | Yes |
| `stock_variant_options` | Pivot: Stock ↔ VariationOption | No |
| `stock_reservations` | Temporary holds on stock | No |

### Relationships

```
Product (external)
    ↓ (1:N)
Stock ──┬── (N:1) → Supplier
        │
        ├── (N:N) → VariationOption ──→ (N:1) → Variant
        │
        └── (1:N) → StockReservation
```

### Key Constraints

- **Supplier Deletion:** RESTRICTED if stocks exist
- **Product Deletion:** CASCADES to stocks
- **Variant Deletion:** CASCADES to variation options
- **Stock Deletion:** CASCADES to reservations

### Indexes

**Performance Indexes:**
- `stocks.sku` (unique)
- `stocks.barcode`
- `stocks.product_id`, `stocks.supplier_id`, `stocks.status`
- `stock_reservations.stock_id`, `stock_reservations.status`
- `stock_reservations.(reference_type, reference_id)` (composite)

---

## Testing

### Using the Seeder

Run the Stock Module seeder to populate sample data:

```bash
# Run ProductPackageSeeder first
php artisan db:seed --class=ProductPackageSeeder

# Then run Stock Module seeder
php artisan db:seed --class=StockModuleSeeder
```

**Sample Data Created:**
- 10 suppliers
- 5 variants with 29 options
- Stock records for all products
- 10 sample reservations with mixed statuses

### Example API Calls

**Create a reservation:**
```bash
curl -X POST http://localhost/api/v1/stock-reservations/reserve \
  -H "Content-Type: application/json" \
  -d '{
    "stock_id": 1,
    "quantity": 2,
    "reference_type": "cart",
    "reference_id": 12345
  }'
```

**Check availability:**
```bash
curl -X POST http://localhost/api/v1/stocks/check-availability \
  -H "Content-Type: application/json" \
  -d '{
    "stock_id": 1,
    "quantity": 50
  }'
```

**Get low stock items:**
```bash
curl http://localhost/api/v1/stocks/low-stock?per_page=20
```

---

## Support & Contact

For issues, questions, or feature requests related to the Stock Module, please contact the development team or create an issue in the project repository.

**Documentation Version:** 1.0.0  
**Last Updated:** December 16, 2025
