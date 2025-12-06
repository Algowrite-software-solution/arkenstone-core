# Product Image Upload Feature

## Overview

The product image upload system allows uploading multiple images to a product with support for metadata, configuration-driven storage, and proper validation.

## API Endpoint

### Upload Images to Product

**Endpoint:** `POST /api/v1/products/{productId}/images/upload`

**Description:** Upload one or more images to an existing product with optional metadata.

#### Request

**Headers:**
```
Content-Type: multipart/form-data
```

**Parameters:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `images[]` | file | Yes | Array of image files to upload |
| `alt_texts[]` | string | No | Array of alt text for each image (max 255 chars) |
| `sort_orders[]` | integer | No | Array of sort order values for each image (min: 0) |
| `primary_index` | integer | No | Zero-based index indicating which image should be primary |

**Example cURL:**
```bash
curl -X POST \
  http://localhost/api/v1/products/1/images/upload \
  -F 'images[]=@/path/to/image1.jpg' \
  -F 'images[]=@/path/to/image2.png' \
  -F 'alt_texts[]=Product front view' \
  -F 'alt_texts[]=Product back view' \
  -F 'sort_orders[]=10' \
  -F 'sort_orders[]=20' \
  -F 'primary_index=0'
```

**JavaScript Example:**
```javascript
const formData = new FormData();
formData.append('images[]', file1);
formData.append('images[]', file2);
formData.append('alt_texts[]', 'Product front view');
formData.append('alt_texts[]', 'Product back view');
formData.append('primary_index', 0);

fetch('/api/v1/products/1/images/upload', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

#### Response

**Success (201 Created):**
```json
{
    "status": "success",
    "message": "Images uploaded successfully",
    "data": [
        {
            "id": 1,
            "product_id": 1,
            "image_url": "http://localhost/storage/products/images/xyz123.jpg",
            "alt_text": "Product front view",
            "is_primary": true,
            "sort_order": 10
        },
        {
            "id": 2,
            "product_id": 1,
            "image_url": "http://localhost/storage/products/images/abc456.png",
            "alt_text": "Product back view",
            "is_primary": false,
            "sort_order": 20
        }
    ]
}
```

**Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Product not found"
}
```

**Validation Error (422 Unprocessable Entity):**
```json
{
    "status": "error",
    "message": "The given data was invalid.",
    "errors": {
        "images.0": ["The images.0 must be an image."],
        "images.1": ["The images.1 may not be greater than 5120 kilobytes."]
    }
}
```

## Configuration

The upload behavior is controlled by `config/arkenstone.php`:

```php
'product_images' => [
    // Storage disk (matches config/filesystems.php disks)
    'disk' => env('ARKENSTONE_IMAGE_DISK', 'public'),
    
    // Base path within the disk
    'path' => env('ARKENSTONE_IMAGE_PATH', 'products/images'),
    
    // Maximum file size in KB
    'max_size' => env('ARKENSTONE_IMAGE_MAX_SIZE', 5120), // 5MB
    
    // Allowed MIME types
    'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    
    // Generate unique filenames (prevents overwrites)
    'unique_filenames' => true,
    
    // Image optimization settings (not yet implemented)
    'optimize' => [
        'enabled' => env('ARKENSTONE_IMAGE_OPTIMIZE', true),
        'quality' => 85, // JPEG quality
    ],
],
```

### Environment Variables

You can override configuration via `.env`:

```env
# Storage disk
ARKENSTONE_IMAGE_DISK=public

# Storage path
ARKENSTONE_IMAGE_PATH=products/images

# Maximum file size in KB (5120 KB = 5 MB)
ARKENSTONE_IMAGE_MAX_SIZE=5120

# Enable image optimization (future feature)
ARKENSTONE_IMAGE_OPTIMIZE=true
```

## Validation Rules

### File Validation

- **Required:** At least one image must be uploaded
- **Type:** Must be a valid image file
- **Size:** Must not exceed configured max size (default: 5MB / 5120KB)
- **Format:** Must be one of: JPEG, PNG, WebP, GIF

### Metadata Validation

- **Alt Text:** Optional, max 255 characters per image
- **Sort Order:** Optional, must be integer ≥ 0
- **Primary Index:** Optional, must be integer ≥ 0, indicates which image is primary

## Storage Behavior

### File Storage

- Images are stored in the configured disk (default: `public`)
- Path: `{configured_path}/{unique_filename}.{extension}`
- Example: `products/images/gH5kL9mN2pQ1.jpg`
- Unique filenames prevent overwrites (configurable)

### Database Records

Each uploaded image creates a `product_images` record with:

- `product_id` - Associated product
- `image_url` - Relative path to the file (e.g., `products/images/abc.jpg`)
- `alt_text` - Optional accessibility text
- `is_primary` - Boolean flag (only one per product)
- `sort_order` - Integer for ordering (default: 0)

## Implementation Details

### Key Components

1. **Request Validation:** `UploadProductImagesRequest`
   - Validates files against config constraints
   - Ensures proper MIME types and file sizes
   
2. **Service Layer:** `ProductService::addImages()`
   - Handles file storage using Laravel Storage
   - Creates database records
   - Supports metadata assignment
   - Dispatches `ProductImagesUploaded` event
   
3. **Controller:** `ProductImageController::upload()`
   - Validates product existence
   - Coordinates upload process
   - Returns `ProductImageResource` collection

4. **Event:** `ProductImagesUploaded`
   - Fired after successful upload
   - Allows hooks for post-processing (e.g., image optimization, CDN sync)

### Fixed Issues

**Critical Bug Fix:** Changed field name from `'url'` to `'image_url'` in `ProductService::addImages()` to match the `ProductImage` model's fillable fields.

**Config Integration:** Storage path now uses `config('arkenstone.product_images.path')` instead of hardcoded `'products'`.

## Usage Examples

### Basic Upload (Single Image)

```php
// PHP/Laravel Example
use Illuminate\Http\UploadedFile;

$image = $request->file('product_image');

$response = Http::attach(
    'images[]', file_get_contents($image->getRealPath()), $image->getClientOriginalName()
)->post('/api/v1/products/1/images/upload');
```

### Multiple Images with Metadata

```php
$response = Http::asMultipart()->post('/api/v1/products/1/images/upload', [
    ['name' => 'images[]', 'contents' => fopen($image1Path, 'r'), 'filename' => 'front.jpg'],
    ['name' => 'images[]', 'contents' => fopen($image2Path, 'r'), 'filename' => 'back.jpg'],
    ['name' => 'alt_texts[]', 'contents' => 'Front view'],
    ['name' => 'alt_texts[]', 'contents' => 'Back view'],
    ['name' => 'primary_index', 'contents' => '0'],
]);
```

### React/Next.js Example

```typescript
const uploadImages = async (productId: number, files: File[]) => {
    const formData = new FormData();
    
    files.forEach(file => {
        formData.append('images[]', file);
    });
    
    formData.append('alt_texts[]', 'Product image 1');
    formData.append('alt_texts[]', 'Product image 2');
    formData.append('primary_index', '0');
    
    const response = await fetch(`/api/v1/products/${productId}/images/upload`, {
        method: 'POST',
        body: formData,
    });
    
    return response.json();
};
```

## Testing

Comprehensive test coverage is provided in `tests/Feature/API/ProductImageUploadTest.php`:

- ✅ Single image upload
- ✅ Multiple images upload
- ✅ Alt text assignment
- ✅ Sort order assignment
- ✅ Primary image designation
- ✅ Validation (required images, file types, size limits, mime types)
- ✅ Product existence validation
- ✅ Config-driven storage path
- ✅ All allowed image formats (JPEG, PNG, WebP, GIF)

Run tests:
```bash
vendor/bin/phpunit tests/Feature/API/ProductImageUploadTest.php
```

## Future Enhancements

1. **Image Optimization:** Implement automatic resizing and compression using `intervention/image` or `spatie/image`
2. **Thumbnail Generation:** Auto-generate multiple sizes (thumb, medium, large)
3. **CDN Integration:** Automatically sync to CDN after upload
4. **Bulk Upload via Product Creation:** Support `uploaded_images[]` in `POST /products` endpoint
5. **Image Cropping:** Allow frontend to specify crop regions before upload
6. **Format Conversion:** Auto-convert to WebP for better compression
7. **Watermarking:** Optional watermark support for product images
