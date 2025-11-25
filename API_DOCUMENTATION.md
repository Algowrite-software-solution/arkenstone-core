# Arkenstone Core - API Documentation

**Version:** 0.1.0  
**Base URL:** `/api/v1`  
**Author:** Algowrite  
**Last Updated:** November 25, 2025

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [API Endpoints](#api-endpoints)
   - [Products](#products)
   - [Brands](#brands)
   - [Categories](#categories)
   - [Taxonomies](#taxonomies)
   - [Taxonomy Types](#taxonomy-types)
   - [Product Images](#product-images)
   - [Product Taxonomies](#product-taxonomies)
6. [Data Models](#data-models)
7. [Filtering & Pagination](#filtering--pagination)
8. [Examples](#examples)

---

## 📖 Overview

The Arkenstone Core API provides a comprehensive e-commerce product management system with support for:
- Product CRUD operations with advanced filtering
- Brand and Category management
- Flexible Taxonomy system for product attributes
- Product image management with primary image support
- Product-Taxonomy relationship management

**Key Features:**
- RESTful API design
- Consistent JSON response format
- Soft delete support for all entities
- Pagination on list endpoints
- Relationship eager loading
- Event-driven architecture

---

## 📦 Response Format

All API responses follow a standardized format using `ResponseProtocol`:

### Success Response
```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    // Validation errors or error details
  }
}
```

### Paginated Response
```json
{
  "status": "success",
  "message": "Items retrieved successfully",
  "data": {
    "data": [
      // Array of items
    ],
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 5,
      "per_page": 15,
      "to": 15,
      "total": 75
    },
    "links": {
      "first": "http://example.com/api/v1/products?page=1",
      "last": "http://example.com/api/v1/products?page=5",
      "prev": null,
      "next": "http://example.com/api/v1/products?page=2"
    }
  }
}
```

---

## ⚠️ Error Handling

### HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET, PUT, DELETE |
| 201 | Created | Successful POST (resource created) |
| 400 | Bad Request | Invalid request format |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server-side error |

### Validation Error Example
```json
{
  "status": "error",
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "price": [
      "The price must be a number.",
      "The price must be at least 0."
    ]
  }
}
```

---

## 🛒 API Endpoints

### Products

#### 1. List All Products
**Endpoint:** `GET /api/v1/products`

**Description:** Retrieve a paginated list of products with optional filtering.

**Query Parameters:**
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `per_page` | integer | No | Items per page (default: 15) | `?per_page=20` |
| `name` | string | No | Filter by product name | `?name=laptop` |
| `min_price` | decimal | No | Minimum price filter | `?min_price=100` |
| `max_price` | decimal | No | Maximum price filter | `?max_price=500` |
| `brand_id` | integer | No | Filter by brand ID | `?brand_id=5` |
| `brand` | integer | No | Alias for brand_id | `?brand=5` |
| `category_ids` | string/array | No | Filter by category IDs (comma-separated or array) | `?category_ids=1,2,3` |
| `is_active` | boolean | No | Filter by active status | `?is_active=1` |

**Response Example:**
```json
{
  "status": "success",
  "message": "Products retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "MacBook Pro 16\"",
        "slug": "macbook-pro-16",
        "description": "Powerful laptop for professionals",
        "price": "2499.99",
        "sale_price": "2249.99",
        "sku": "MBP-16-2023",
        "quantity": 15,
        "discount_type": "percentage",
        "discount_value": "10.00",
        "is_active": true,
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-15T14:30:00.000000Z",
        "brand": {
          "id": 1,
          "name": "Apple",
          "slug": "apple"
        },
        "categories": [
          {
            "id": 1,
            "name": "Laptops",
            "slug": "laptops"
          }
        ],
        "images": [
          {
            "id": 1,
            "image_url": "http://example.com/storage/products/macbook-1.jpg",
            "alt_text": "MacBook Pro front view",
            "is_primary": true
          }
        ]
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 15,
      "total": 50
    }
  }
}
```

---

#### 2. Create Product
**Endpoint:** `POST /api/v1/products`

**Description:** Create a new product.

**Request Body:**
```json
{
  "name": "MacBook Pro 16\"",
  "slug": "macbook-pro-16",
  "description": "Powerful laptop for professionals",
  "price": 2499.99,
  "sku": "MBP-16-2023",
  "quantity": 15,
  "brand_id": 1,
  "is_active": true,
  "discount_type": "percentage",
  "discount_value": 10,
  "category_ids": [1, 2],
  "taxonomy_ids": [5, 6, 7]
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255 |
| `slug` | string | Yes | max:255, unique:products |
| `description` | text | No | - |
| `price` | decimal | Yes | numeric, min:0 |
| `sku` | string | Yes | max:255, unique:products |
| `quantity` | integer | No | integer, min:0 |
| `brand_id` | integer | Yes | exists:brands,id |
| `is_active` | boolean | No | boolean |
| `discount_type` | enum | No | in:percentage,fixed_amount |
| `discount_value` | decimal | No | numeric, min:0 |
| `category_ids` | array | No | array |
| `category_ids.*` | integer | No | exists:categories,id |
| `taxonomy_ids` | array | No | array |
| `taxonomy_ids.*` | integer | No | exists:taxonomies,id |

**Response (201):**
```json
{
  "status": "success",
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "MacBook Pro 16\"",
    "slug": "macbook-pro-16",
    "price": "2499.99",
    "sale_price": "2249.99",
    // ... full product data
  }
}
```

---

#### 3. Show Product
**Endpoint:** `GET /api/v1/products/{id}`

**Description:** Retrieve a single product by ID with relationships.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Product ID |

**Relationships Loaded:**
- `brand` - Product brand information
- `categories` - Associated categories
- `taxonomies` - Associated taxonomies
- `images` - All product images
- `primaryImage` - Primary product image

**Response (200):**
```json
{
  "status": "success",
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "name": "MacBook Pro 16\"",
    "slug": "macbook-pro-16",
    "description": "Powerful laptop for professionals",
    "price": "2499.99",
    "sale_price": "2249.99",
    "sku": "MBP-16-2023",
    "quantity": 15,
    "discount_type": "percentage",
    "discount_value": "10.00",
    "is_active": true,
    "brand": {
      "id": 1,
      "name": "Apple",
      "slug": "apple",
      "description": "Think Different",
      "is_active": true
    },
    "categories": [
      {
        "id": 1,
        "name": "Laptops",
        "slug": "laptops"
      },
      {
        "id": 2,
        "name": "Electronics",
        "slug": "electronics"
      }
    ],
    "taxonomies": [
      {
        "id": 5,
        "name": "Silver",
        "type": {
          "id": 1,
          "name": "Color",
          "slug": "color"
        }
      }
    ],
    "images": [
      {
        "id": 1,
        "image_url": "http://example.com/storage/products/macbook-1.jpg",
        "alt_text": "MacBook Pro front view",
        "is_primary": true,
        "sort_order": 1
      }
    ],
    "primary_image": {
      "id": 1,
      "image_url": "http://example.com/storage/products/macbook-1.jpg",
      "alt_text": "MacBook Pro front view"
    }
  }
}
```

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Product not found",
  "errors": null
}
```

---

#### 4. Update Product
**Endpoint:** `PUT /api/v1/products/{id}`

**Description:** Update an existing product.

**Request Body:** (All fields optional)
```json
{
  "name": "MacBook Pro 16\" M3",
  "price": 2699.99,
  "quantity": 20,
  "is_active": true,
  "discount_type": "fixed_amount",
  "discount_value": 200,
  "category_ids": [1, 2, 3],
  "taxonomy_ids": [5, 6]
}
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Product updated successfully",
  "data": {
    // Updated product data
  }
}
```

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Product not found",
  "errors": null
}
```

---

#### 5. Delete Product
**Endpoint:** `DELETE /api/v1/products/{id}`

**Description:** Soft delete a product (can be restored).

**Response (200):**
```json
{
  "status": "success",
  "message": "Product deleted successfully",
  "data": null
}
```

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Product not found",
  "errors": null
}
```

---

### Brands

#### 1. List All Brands
**Endpoint:** `GET /api/v1/brands`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 15) |

**Response (200):**
```json
{
  "status": "success",
  "message": "Brands retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Apple",
        "slug": "apple",
        "description": "Think Different",
        "logo": "brands/apple-logo.png",
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 15,
      "total": 25
    }
  }
}
```

---

#### 2. Create Brand
**Endpoint:** `POST /api/v1/brands`

**Request Body:**
```json
{
  "name": "Apple",
  "slug": "apple",
  "description": "Think Different",
  "logo": "brands/apple-logo.png",
  "is_active": true
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255, unique:brands |
| `slug` | string | Yes | max:255, unique:brands |
| `description` | text | No | - |
| `logo` | string | No | max:255 |
| `is_active` | boolean | No | boolean |

**Response (201):**
```json
{
  "status": "success",
  "message": "Brand created successfully",
  "data": {
    "id": 1,
    "name": "Apple",
    "slug": "apple",
    // ... full brand data
  }
}
```

---

#### 3. Show Brand
**Endpoint:** `GET /api/v1/brands/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Brand retrieved successfully",
  "data": {
    "id": 1,
    "name": "Apple",
    "slug": "apple",
    "description": "Think Different",
    "logo": "brands/apple-logo.png",
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

#### 4. Update Brand
**Endpoint:** `PUT /api/v1/brands/{id}`

**Request Body:** (All fields optional)
```json
{
  "name": "Apple Inc.",
  "description": "Innovation at its finest",
  "is_active": true
}
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Brand updated successfully",
  "data": {
    // Updated brand data
  }
}
```

---

#### 5. Delete Brand
**Endpoint:** `DELETE /api/v1/brands/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Brand deleted successfully",
  "data": null
}
```

---

### Categories

#### 1. List All Categories
**Endpoint:** `GET /api/v1/categories`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 15) |

**Response (200):**
```json
{
  "status": "success",
  "message": "Categories retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Electronics",
        "slug": "electronics",
        "description": "Electronic devices and gadgets",
        "parent_id": null,
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "children": [
          {
            "id": 2,
            "name": "Laptops",
            "slug": "laptops",
            "parent_id": 1
          }
        ]
      }
    ]
  }
}
```

---

#### 2. Get Root Categories
**Endpoint:** `GET /api/v1/categories/roots`

**Description:** Retrieve all top-level categories (parent_id = null).

**Response (200):**
```json
{
  "status": "success",
  "message": "Root categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "parent_id": null,
      "is_active": true
    }
  ]
}
```

---

#### 3. Get Category Children
**Endpoint:** `GET /api/v1/categories/{id}/children`

**Description:** Retrieve all direct children of a category.

**Response (200):**
```json
{
  "status": "success",
  "message": "Category children retrieved successfully",
  "data": [
    {
      "id": 2,
      "name": "Laptops",
      "slug": "laptops",
      "parent_id": 1,
      "is_active": true
    },
    {
      "id": 3,
      "name": "Smartphones",
      "slug": "smartphones",
      "parent_id": 1,
      "is_active": true
    }
  ]
}
```

---

#### 4. Create Category
**Endpoint:** `POST /api/v1/categories`

**Request Body:**
```json
{
  "name": "Laptops",
  "slug": "laptops",
  "description": "Portable computers",
  "parent_id": 1,
  "is_active": true
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255, unique:categories |
| `slug` | string | Yes | max:255, unique:categories |
| `description` | text | No | - |
| `parent_id` | integer | No | exists:categories,id, not same as id (self-reference check) |
| `is_active` | boolean | No | boolean |

**Response (201):**
```json
{
  "status": "success",
  "message": "Category created successfully",
  "data": {
    "id": 2,
    "name": "Laptops",
    "slug": "laptops",
    "parent_id": 1
  }
}
```

---

#### 5. Show Category
**Endpoint:** `GET /api/v1/categories/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Category retrieved successfully",
  "data": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices",
    "parent_id": null,
    "is_active": true,
    "parent": null,
    "children": [
      {
        "id": 2,
        "name": "Laptops",
        "slug": "laptops"
      }
    ]
  }
}
```

---

#### 6. Update Category
**Endpoint:** `PUT /api/v1/categories/{id}`

**Request Body:** (All fields optional)
```json
{
  "name": "Electronics & Gadgets",
  "description": "All electronic devices",
  "is_active": false
}
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Category updated successfully",
  "data": {
    // Updated category data
  }
}
```

---

#### 7. Delete Category
**Endpoint:** `DELETE /api/v1/categories/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Category deleted successfully",
  "data": null
}
```

---

### Taxonomies

Taxonomies are flexible attribute systems for products (e.g., Colors, Sizes, Materials).

#### 1. List All Taxonomies
**Endpoint:** `GET /api/v1/taxonomies`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 15) |
| `taxonomy_type_id` | integer | No | Filter by taxonomy type ID |
| `type_slug` | string | No | Filter by taxonomy type slug |
| `parent_id` | integer | No | Filter by parent taxonomy ID |
| `root_only` | boolean | No | Get only root taxonomies (no parent) |
| `search` | string | No | Search by taxonomy name |

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomies retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Red",
        "slug": "red",
        "description": "Red color",
        "taxonomy_type_id": 1,
        "parent_id": null,
        "sort_order": 1,
        "is_active": true,
        "meta": {},
        "type": {
          "id": 1,
          "name": "Color",
          "slug": "color"
        },
        "parent": null,
        "children": []
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 15,
      "total": 50
    }
  }
}
```

---

#### 2. Get Taxonomies by Type
**Endpoint:** `GET /api/v1/taxonomies/type/{typeId}`

**Description:** Retrieve all taxonomies of a specific type.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `typeId` | integer | Yes | Taxonomy Type ID |

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomies retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Red",
        "slug": "red",
        "taxonomy_type_id": 1
      },
      {
        "id": 2,
        "name": "Blue",
        "slug": "blue",
        "taxonomy_type_id": 1
      }
    ]
  }
}
```

---

#### 3. Create Taxonomy
**Endpoint:** `POST /api/v1/taxonomies`

**Request Body:**
```json
{
  "taxonomy_type_id": 1,
  "name": "Red",
  "slug": "red",
  "description": "Red color variant",
  "parent_id": null,
  "sort_order": 1,
  "meta": {
    "hex_code": "#FF0000"
  },
  "is_active": true
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `taxonomy_type_id` | integer | Yes | exists:taxonomy_types,id |
| `name` | string | Yes | max:255 |
| `slug` | string | Yes | max:255, unique:taxonomies |
| `description` | text | No | - |
| `parent_id` | integer | No | exists:taxonomies,id, not self-reference |
| `sort_order` | integer | No | integer |
| `meta` | object | No | json |
| `is_active` | boolean | No | boolean |

**Response (201):**
```json
{
  "status": "success",
  "message": "Taxonomy created successfully",
  "data": {
    "id": 1,
    "name": "Red",
    "slug": "red",
    "taxonomy_type_id": 1
  }
}
```

---

#### 4. Show Taxonomy
**Endpoint:** `GET /api/v1/taxonomies/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomy retrieved successfully",
  "data": {
    "id": 1,
    "name": "Red",
    "slug": "red",
    "description": "Red color variant",
    "taxonomy_type_id": 1,
    "parent_id": null,
    "sort_order": 1,
    "meta": {
      "hex_code": "#FF0000"
    },
    "is_active": true,
    "type": {
      "id": 1,
      "name": "Color",
      "slug": "color"
    },
    "parent": null,
    "children": []
  }
}
```

---

#### 5. Update Taxonomy
**Endpoint:** `PUT /api/v1/taxonomies/{id}`

**Request Body:** (All fields optional)
```json
{
  "name": "Bright Red",
  "description": "Vibrant red color",
  "sort_order": 2
}
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomy updated successfully",
  "data": {
    // Updated taxonomy data
  }
}
```

---

#### 6. Delete Taxonomy
**Endpoint:** `DELETE /api/v1/taxonomies/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomy deleted successfully",
  "data": null
}
```

---

### Taxonomy Types

Taxonomy Types define the categories of taxonomies (e.g., Color, Size, Material).

#### 1. List All Taxonomy Types
**Endpoint:** `GET /api/v1/taxonomy-types`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 15) |

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomy types retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Color",
        "slug": "color",
        "description": "Product color attributes",
        "is_active": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "taxonomies": [
          {
            "id": 1,
            "name": "Red",
            "slug": "red"
          },
          {
            "id": 2,
            "name": "Blue",
            "slug": "blue"
          }
        ]
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 15,
      "total": 10
    }
  }
}
```

---

#### 2. Create Taxonomy Type
**Endpoint:** `POST /api/v1/taxonomy-types`

**Request Body:**
```json
{
  "name": "Color",
  "slug": "color",
  "description": "Product color attributes",
  "is_active": true
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255, unique:taxonomy_types |
| `slug` | string | Yes | max:255, unique:taxonomy_types |
| `description` | text | No | - |
| `is_active` | boolean | No | boolean |

**Response (201):**
```json
{
  "status": "success",
  "message": "Taxonomy type created successfully",
  "data": {
    "id": 1,
    "name": "Color",
    "slug": "color",
    "is_active": true
  }
}
```

---

#### 3. Show Taxonomy Type
**Endpoint:** `GET /api/v1/taxonomy-types/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomy type retrieved successfully",
  "data": {
    "id": 1,
    "name": "Color",
    "slug": "color",
    "description": "Product color attributes",
    "is_active": true,
    "taxonomies": [
      {
        "id": 1,
        "name": "Red",
        "slug": "red"
      }
    ]
  }
}
```

---

#### 4. Update Taxonomy Type
**Endpoint:** `PUT /api/v1/taxonomy-types/{id}`

**Request Body:** (All fields optional)
```json
{
  "name": "Product Colors",
  "description": "Color variants for products"
}
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomy type updated successfully",
  "data": {
    // Updated taxonomy type data
  }
}
```

---

#### 5. Delete Taxonomy Type
**Endpoint:** `DELETE /api/v1/taxonomy-types/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomy type deleted successfully",
  "data": null
}
```

---

### Product Images

#### 1. List Product Images
**Endpoint:** `GET /api/v1/products/{productId}/images`

**Description:** Get all images for a specific product.

**Response (200):**
```json
{
  "status": "success",
  "message": "Product images retrieved successfully",
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "image_url": "http://example.com/storage/products/image-1.jpg",
      "alt_text": "Product front view",
      "is_primary": true,
      "sort_order": 1,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

#### 2. Create Product Image
**Endpoint:** `POST /api/v1/product-images`

**Request Body:**
```json
{
  "product_id": 1,
  "image_url": "products/macbook-front.jpg",
  "alt_text": "MacBook Pro front view",
  "is_primary": true,
  "sort_order": 1
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | Yes | exists:products,id |
| `image_url` | string | Yes | max:255 |
| `alt_text` | string | No | max:255 |
| `is_primary` | boolean | No | boolean |
| `sort_order` | integer | No | integer |

**Response (201):**
```json
{
  "status": "success",
  "message": "Product image created successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "image_url": "http://example.com/storage/products/macbook-front.jpg",
    "alt_text": "MacBook Pro front view",
    "is_primary": true,
    "sort_order": 1
  }
}
```

---

#### 3. Show Product Image
**Endpoint:** `GET /api/v1/product-images/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Product image retrieved successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "image_url": "http://example.com/storage/products/macbook-front.jpg",
    "alt_text": "MacBook Pro front view",
    "is_primary": true,
    "sort_order": 1,
    "product": {
      "id": 1,
      "name": "MacBook Pro 16\"",
      "slug": "macbook-pro-16"
    }
  }
}
```

---

#### 4. Update Product Image
**Endpoint:** `PUT /api/v1/product-images/{id}`

**Request Body:** (All fields optional)
```json
{
  "alt_text": "Updated alt text",
  "is_primary": false,
  "sort_order": 2
}
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Product image updated successfully",
  "data": {
    // Updated image data
  }
}
```

---

#### 5. Delete Product Image
**Endpoint:** `DELETE /api/v1/product-images/{id}`

**Response (200):**
```json
{
  "status": "success",
  "message": "Product image deleted successfully",
  "data": null
}
```

---

#### 6. Set Primary Image
**Endpoint:** `POST /api/v1/products/{productId}/images/{imageId}/set-primary`

**Description:** Set an image as the primary image for a product.

**Response (200):**
```json
{
  "status": "success",
  "message": "Primary image set successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "is_primary": true
  }
}
```

---

#### 7. Get Primary Image
**Endpoint:** `GET /api/v1/products/{productId}/primary-image`

**Description:** Retrieve the primary image for a product.

**Response (200):**
```json
{
  "status": "success",
  "message": "Primary image retrieved successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "image_url": "http://example.com/storage/products/macbook-front.jpg",
    "alt_text": "MacBook Pro front view",
    "is_primary": true
  }
}
```

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Primary image not found",
  "errors": null
}
```

---

### Product Taxonomies

Manage many-to-many relationships between products and taxonomies.

#### 1. Get Product Taxonomies
**Endpoint:** `GET /api/v1/products/{product}/taxonomies`

**Description:** Get all taxonomies attached to a product.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `type_id` | integer | No | Filter by taxonomy type ID |

**Response (200):**
```json
{
  "status": "success",
  "message": "Product taxonomies retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Red",
      "slug": "red",
      "taxonomy_type_id": 1,
      "type": {
        "id": 1,
        "name": "Color",
        "slug": "color"
      }
    },
    {
      "id": 5,
      "name": "Large",
      "slug": "large",
      "taxonomy_type_id": 2,
      "type": {
        "id": 2,
        "name": "Size",
        "slug": "size"
      }
    }
  ]
}
```

---

#### 2. Get Products by Taxonomy
**Endpoint:** `GET /api/v1/taxonomies/{taxonomy}/products`

**Description:** Get all products that have a specific taxonomy.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `with` | array | No | Eager load relationships (e.g., `with[]=brand&with[]=categories`) |

**Response (200):**
```json
{
  "status": "success",
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "MacBook Pro 16\"",
      "slug": "macbook-pro-16",
      "price": "2499.99",
      "brand": {
        "id": 1,
        "name": "Apple",
        "slug": "apple"
      }
    }
  ]
}
```

---

#### 3. Attach Taxonomies to Product
**Endpoint:** `POST /api/v1/products/taxonomies/attach`

**Description:** Attach one or more taxonomies to a product.

**Request Body:**
```json
{
  "product_id": 1,
  "taxonomy_ids": [1, 2, 3]
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | Yes | exists:products,id |
| `taxonomy_ids` | array | Yes | array, min:1 |
| `taxonomy_ids.*` | integer | Yes | exists:taxonomies,id |

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomies attached to product successfully",
  "data": {
    "attached": [1, 3],
    "already_attached": [2]
  }
}
```

**Validation Error Response (422):**
```json
{
  "status": "error",
  "message": "The given data was invalid.",
  "errors": {
    "product_id": [
      "The selected product does not exist."
    ],
    "taxonomy_ids": [
      "At least one taxonomy must be provided."
    ]
  }
}
```

---

#### 4. Sync Taxonomies to Product
**Endpoint:** `POST /api/v1/products/taxonomies/sync`

**Description:** Replace all existing taxonomies with the provided ones. Passing an empty array will detach all taxonomies.

**Request Body:**
```json
{
  "product_id": 1,
  "taxonomy_ids": [1, 2, 3]
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | Yes | exists:products,id |
| `taxonomy_ids` | array | No | array (can be empty) |
| `taxonomy_ids.*` | integer | Yes (if array not empty) | exists:taxonomies,id |

**Response (200):**
```json
{
  "status": "success",
  "message": "Product taxonomies synchronized successfully",
  "data": null
}
```

**Example - Detach All:**
```json
{
  "product_id": 1,
  "taxonomy_ids": []
}
```

---

#### 5. Detach Taxonomies from Product
**Endpoint:** `POST /api/v1/products/taxonomies/detach`

**Description:** Remove one or more taxonomies from a product.

**Request Body:**
```json
{
  "product_id": 1,
  "taxonomy_ids": [1, 2, 3]
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | Yes | exists:products,id |
| `taxonomy_ids` | array | Yes | array, min:1 |
| `taxonomy_ids.*` | integer | Yes | exists:taxonomies,id |

**Response (200):**
```json
{
  "status": "success",
  "message": "Taxonomies detached from product successfully",
  "data": {
    "detached": [1, 2],
    "not_found": [3]
  }
}
```

**Note:** `not_found` contains taxonomy IDs that were not attached to the product in the first place.

---

## 📊 Data Models

### Product Model

**Table:** `products`

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `name` | string(255) | No | Product name |
| `slug` | string(255) | No | URL-friendly identifier (unique) |
| `description` | text | Yes | Product description |
| `price` | decimal(10,2) | No | Base price |
| `sku` | string(255) | No | Stock Keeping Unit (unique) |
| `quantity` | integer | No | Available quantity (default: 0) |
| `discount_type` | enum | Yes | Discount type: `percentage`, `fixed_amount` |
| `discount_value` | decimal(10,2) | Yes | Discount amount/percentage |
| `brand_id` | bigint | No | Foreign key to brands table |
| `is_active` | boolean | No | Active status (default: true) |
| `created_at` | timestamp | No | Creation timestamp |
| `updated_at` | timestamp | No | Last update timestamp |
| `deleted_at` | timestamp | Yes | Soft delete timestamp |

**Relationships:**
- `brand` - BelongsTo Brand
- `categories` - BelongsToMany Category (via `product_categories`)
- `taxonomies` - BelongsToMany Taxonomy (via `product_taxonomies`)
- `images` - HasMany ProductImage
- `primaryImage` - HasOne ProductImage (where `is_primary` = true)

**Computed Attributes:**
- `sale_price` - Calculated price after discount

**Query Scopes:**
- `isActive()` - Filter active products
- `filterByName($name)` - Search by name
- `minPrice($price)` - Minimum price filter
- `maxPrice($price)` - Maximum price filter
- `byBrand($id)` - Filter by brand
- `byCategories($ids)` - Filter by categories (OR logic)
- `byAllCategories($ids)` - Filter by all categories (AND logic)

---

### Brand Model

**Table:** `brands`

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `name` | string(255) | No | Brand name (unique) |
| `slug` | string(255) | No | URL-friendly identifier (unique) |
| `description` | text | Yes | Brand description |
| `logo` | string(255) | Yes | Logo image path |
| `is_active` | boolean | No | Active status (default: true) |
| `created_at` | timestamp | No | Creation timestamp |
| `updated_at` | timestamp | No | Last update timestamp |
| `deleted_at` | timestamp | Yes | Soft delete timestamp |

**Relationships:**
- `products` - HasMany Product

---

### Category Model

**Table:** `categories`

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `name` | string(255) | No | Category name (unique) |
| `slug` | string(255) | No | URL-friendly identifier (unique) |
| `description` | text | Yes | Category description |
| `parent_id` | bigint | Yes | Parent category ID (self-reference) |
| `is_active` | boolean | No | Active status (default: true) |
| `created_at` | timestamp | No | Creation timestamp |
| `updated_at` | timestamp | No | Last update timestamp |
| `deleted_at` | timestamp | Yes | Soft delete timestamp |

**Relationships:**
- `products` - BelongsToMany Product (via `product_categories`)
- `parent` - BelongsTo Category (self)
- `children` - HasMany Category (self)

---

### Taxonomy Model

**Table:** `taxonomies`

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `taxonomy_type_id` | bigint | No | Foreign key to taxonomy_types |
| `parent_id` | bigint | Yes | Parent taxonomy ID (self-reference) |
| `name` | string(255) | No | Taxonomy name |
| `slug` | string(255) | No | URL-friendly identifier (unique) |
| `description` | text | Yes | Taxonomy description |
| `sort_order` | integer | Yes | Display order |
| `meta` | json | Yes | Additional metadata |
| `is_active` | boolean | No | Active status (default: true) |
| `created_at` | timestamp | No | Creation timestamp |
| `updated_at` | timestamp | No | Last update timestamp |
| `deleted_at` | timestamp | Yes | Soft delete timestamp |

**Relationships:**
- `taxonomyType` / `type` - BelongsTo TaxonomyType
- `parent` - BelongsTo Taxonomy (self)
- `children` - HasMany Taxonomy (self)
- `products` - BelongsToMany Product (via `product_taxonomies`)

**Query Scopes:**
- `byType($typeId)` - Filter by taxonomy type
- `isActive()` - Filter active taxonomies
- `filterByName($name)` - Search by name

---

### Taxonomy Type Model

**Table:** `taxonomy_types`

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `name` | string(255) | No | Type name (unique) |
| `slug` | string(255) | No | URL-friendly identifier (unique) |
| `description` | text | Yes | Type description |
| `is_active` | boolean | No | Active status (default: true) |
| `created_at` | timestamp | No | Creation timestamp |
| `updated_at` | timestamp | No | Last update timestamp |
| `deleted_at` | timestamp | Yes | Soft delete timestamp |

**Relationships:**
- `taxonomies` - HasMany Taxonomy

---

### Product Image Model

**Table:** `product_images`

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `product_id` | bigint | No | Foreign key to products |
| `image_url` | string(255) | No | Image file path |
| `alt_text` | string(255) | Yes | Alt text for accessibility |
| `is_primary` | boolean | No | Primary image flag (default: false) |
| `sort_order` | integer | Yes | Display order |
| `created_at` | timestamp | No | Creation timestamp |
| `updated_at` | timestamp | No | Last update timestamp |
| `deleted_at` | timestamp | Yes | Soft delete timestamp |

**Relationships:**
- `product` - BelongsTo Product

---

### Product Taxonomy (Pivot)

**Table:** `product_taxonomies`

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `product_id` | bigint | No | Foreign key to products |
| `taxonomy_id` | bigint | No | Foreign key to taxonomies |
| `created_at` | timestamp | No | Creation timestamp |
| `updated_at` | timestamp | No | Last update timestamp |

**Indexes:**
- Unique: (`product_id`, `taxonomy_id`)

---

## 🔍 Filtering & Pagination

### Pagination

All list endpoints support pagination with the following query parameter:

**Parameter:** `per_page`  
**Type:** integer  
**Default:** 15  
**Range:** 1-100

**Example:**
```
GET /api/v1/products?per_page=25
```

### Product Filtering

The `/api/v1/products` endpoint supports advanced filtering:

**Example - Multiple Filters:**
```
GET /api/v1/products?name=laptop&min_price=500&max_price=2000&brand_id=1&category_ids=1,2&is_active=1&per_page=20
```

**Filter Combinations:**
- Price range: `min_price` + `max_price`
- Category filtering: `category_ids` (comma-separated or array)
- Brand filtering: `brand_id` or `brand`
- Search: `name` (partial match)
- Status: `is_active` (1 or 0)

### Taxonomy Filtering

The `/api/v1/taxonomies` endpoint supports:

**Example - Complex Filter:**
```
GET /api/v1/taxonomies?type_slug=color&root_only=true&search=red&per_page=10
```

**Filter Combinations:**
- By type: `taxonomy_type_id` or `type_slug`
- Hierarchy: `parent_id` or `root_only`
- Search: `search` (partial name match)

---

## 💡 Examples

### Example 1: Create Product with Relationships

**Request:**
```bash
curl -X POST http://example.com/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "MacBook Pro 16\" M3",
    "slug": "macbook-pro-16-m3",
    "description": "Professional laptop with M3 chip",
    "price": 2699.99,
    "sku": "MBP-16-M3-2024",
    "quantity": 10,
    "brand_id": 1,
    "discount_type": "percentage",
    "discount_value": 10,
    "category_ids": [1, 2],
    "taxonomy_ids": [5, 8, 12],
    "is_active": true
  }'
```

**Response:**
```json
{
  "status": "success",
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "MacBook Pro 16\" M3",
    "slug": "macbook-pro-16-m3",
    "price": "2699.99",
    "sale_price": "2429.99",
    "discount_type": "percentage",
    "discount_value": "10.00"
  }
}
```

---

### Example 2: Search Products with Filters

**Request:**
```bash
curl -X GET "http://example.com/api/v1/products?name=macbook&min_price=1000&max_price=3000&brand_id=1&category_ids=1,2&per_page=10"
```

**Response:**
```json
{
  "status": "success",
  "message": "Products retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "MacBook Pro 16\" M3",
        "price": "2699.99",
        "sale_price": "2429.99"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 10,
      "total": 3
    }
  }
}
```

---

### Example 3: Manage Product Taxonomies

**Step 1: Attach Taxonomies**
```bash
curl -X POST http://example.com/api/v1/products/taxonomies/attach \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "taxonomy_ids": [1, 2, 3]
  }'
```

**Step 2: Sync Taxonomies (Replace All)**
```bash
curl -X POST http://example.com/api/v1/products/taxonomies/sync \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "taxonomy_ids": [4, 5]
  }'
```

**Step 3: Detach Specific Taxonomies**
```bash
curl -X POST http://example.com/api/v1/products/taxonomies/detach \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "taxonomy_ids": [4]
  }'
```

---

### Example 4: Create Hierarchical Categories

**Step 1: Create Parent Category**
```bash
curl -X POST http://example.com/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "slug": "electronics",
    "description": "All electronic devices",
    "is_active": true
  }'
```

**Step 2: Create Child Category**
```bash
curl -X POST http://example.com/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptops",
    "slug": "laptops",
    "parent_id": 1,
    "is_active": true
  }'
```

**Step 3: Get Category Tree**
```bash
curl -X GET "http://example.com/api/v1/categories/1/children"
```

---

### Example 5: Work with Product Images

**Step 1: Upload Image**
```bash
curl -X POST http://example.com/api/v1/product-images \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "image_url": "products/macbook-front.jpg",
    "alt_text": "MacBook Pro front view",
    "is_primary": true,
    "sort_order": 1
  }'
```

**Step 2: Set as Primary**
```bash
curl -X POST http://example.com/api/v1/products/1/images/5/set-primary
```

**Step 3: Get All Product Images**
```bash
curl -X GET http://example.com/api/v1/products/1/images
```

---

## 🚀 Best Practices

### 1. Always Use Pagination
```javascript
// Good
fetch('/api/v1/products?per_page=20')

// Bad (loads all products)
fetch('/api/v1/products')
```

### 2. Eager Load Relationships
```javascript
// Efficient - loads relationships in one query
fetch('/api/v1/taxonomies/1/products?with[]=brand&with[]=categories')
```

### 3. Handle Errors Properly
```javascript
fetch('/api/v1/products', {
  method: 'POST',
  body: JSON.stringify(data)
})
.then(res => res.json())
.then(data => {
  if (data.status === 'error') {
    // Handle validation errors
    console.error(data.errors);
  } else {
    // Success
    console.log(data.data);
  }
});
```

### 4. Use Bulk Operations
```javascript
// Good - sync all taxonomies at once
POST /api/v1/products/taxonomies/sync
{ "product_id": 1, "taxonomy_ids": [1,2,3,4,5] }

// Bad - attach one by one (5 requests)
POST /api/v1/products/taxonomies/attach
{ "product_id": 1, "taxonomy_ids": [1] }
```

---

## 📞 Support

**Repository:** [github.com/Algowrite-software-solution/arkenstone-core](https://github.com/Algowrite-software-solution/arkenstone-core)  
**Issues:** Use GitHub Issues for bug reports  
**Documentation:** Check `README.md` for setup instructions

---

## 📝 Changelog

### Version 0.1.0 (November 25, 2025)
- Initial API release
- Product CRUD with filtering
- Brand, Category, Taxonomy management
- Product Image management
- Product-Taxonomy relationship operations
- Comprehensive test coverage (160 tests)

---

**Last Updated:** November 25, 2025  
**Document Version:** 0.1.0
