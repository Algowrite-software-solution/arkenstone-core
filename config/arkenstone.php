<?php

return [
    'enabled' => env('ARKENSTONE_CORE_ENABLED', true),
    'default_prefix' => env('ARKENSTONE_CORE_PREFIX', '[ARKENSTONE_CORE]'),

    /*
     |--------------------------------------------------------------------------
     | Product Image Storage Configuration
     |--------------------------------------------------------------------------
     */
    'product_images' => [
        // Storage disk (matches config/filesystems.php disks)
        'disk' => env('ARKENSTONE_IMAGE_DISK', 'public'),

        // Base path within the disk
        'path' => env('ARKENSTONE_IMAGE_PATH', 'images'),

        // Maximum file size in KB
        'max_size' => env('ARKENSTONE_IMAGE_MAX_SIZE', 5120), // 5MB

        // Allowed MIME types
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],

        // Generate unique filenames (prevents overwrites)
        'unique_filenames' => true,

        // Image optimization settings
        'optimize' => [
            'enabled' => env('ARKENSTONE_IMAGE_OPTIMIZE', true),
            'quality' => 85, // JPEG quality
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Category Image Storage Configuration
     |--------------------------------------------------------------------------
     */
    'category_images' => [
        // Storage disk
        'disk' => env('ARKENSTONE_CATEGORY_IMAGE_DISK', 'categories'),

        // Base path within the disk
        'path' => env('ARKENSTONE_CATEGORY_IMAGE_PATH', 'categories/images'),

        // Maximum file size in KB
        'max_size' => env('ARKENSTONE_CATEGORY_IMAGE_MAX_SIZE', 5120), // 5MB

        // Allowed MIME types
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],

        // Generate unique filenames (prevents overwrites)
        'unique_filenames' => true,

        // Image optimization settings
        'optimize' => [
            'enabled' => env('ARKENSTONE_CATEGORY_IMAGE_OPTIMIZE', true),
            'quality' => 85, // JPEG quality
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | API Defaults Configuration
     |--------------------------------------------------------------------------
     */
    'api_defaults' => [
        'per_page' => 100000,
        'order' => 'desc',
    ],


    /*
    |--------------------------------------------------------------------------
    | Entity Record Lock Configuration
    |--------------------------------------------------------------------------
    */
    'entity_record_lock' => [
        // Prevent deleting category if it has products
        'categories' => [
            'Locked Category',
            // 'cateogry 2'
        ],
        'taxonomies' => [
            'Locked Taxonomy',
            // 'taxonomy 2'
        ],
        'taxonomy_types' => [
            'Locked Taxonomy Type',
            // 'type 2'
        ],
    ],
];