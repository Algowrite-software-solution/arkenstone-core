<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $config = config('arkenstone.product_images');
        $maxSize = $config['max_size'] ?? 5120;
        $allowedMimes = $this->extractMimeExtensions($config['allowed_types'] ?? []);

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'minified_description' => ['nullable', 'string', 'max:500'],
            'details' => ['nullable', 'array'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:percentage,fixed_amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'required_with:discount_type'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'taxonomy_ids' => ['nullable', 'array'],
            'taxonomy_ids.*' => ['integer', 'exists:taxonomies,id'],

            // Image upload fields
            'uploaded_images' => ['nullable', 'array'],
            'uploaded_images.*' => [
                'required',
                'file',
                'image',
                'max:' . $maxSize,
                'mimes:' . implode(',', $allowedMimes),
            ],
            'image_alt_texts' => ['nullable', 'array'],
            'image_alt_texts.*' => ['nullable', 'string', 'max:255'],
            'image_sort_orders' => ['nullable', 'array'],
            'image_sort_orders.*' => ['nullable', 'integer', 'min:0'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'minified_description.max' => 'Minified description must not exceed 500 characters.',
            'details.array' => 'Details must be a valid array.',
            'price.required' => 'Product price is required.',
            'price.min' => 'Product price must be greater than or equal to 0.',
            'discount_type.in' => 'Discount type must be either percentage or fixed_amount.',
            'discount_value.required_with' => 'Discount value is required when discount type is set.',
            'discount_value.min' => 'Discount value must be greater than or equal to 0.',
            'sku.unique' => 'This SKU is already in use.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
            'taxonomy_ids.*.exists' => 'One or more selected taxonomies do not exist.',
            'uploaded_images.*.file' => 'Each upload must be a valid file.',
            'uploaded_images.*.image' => 'Each file must be a valid image.',
            'uploaded_images.*.max' => 'Each image must not exceed the maximum file size.',
            'uploaded_images.*.mimes' => 'Each image must be a valid format (jpeg, png, webp, gif).',
            'image_alt_texts.*.max' => 'Alt text must not exceed 255 characters.',
            'image_sort_orders.*.min' => 'Sort order must be greater than or equal to 0.',
            'primary_image_index.integer' => 'Primary image index must be an integer.',
            'primary_image_index.min' => 'Primary image index must be greater than or equal to 0.',
        ];
    }

    /**
     * Extract file extensions from MIME types.
     *
     * @param array $mimeTypes
     * @return array
     */
    private function extractMimeExtensions(array $mimeTypes): array
    {
        $extensions = [];
        $mimeMap = [
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        foreach ($mimeTypes as $mime) {
            if (isset($mimeMap[$mime])) {
                $extensions[] = $mimeMap[$mime];
            }
        }

        if (in_array('jpeg', $extensions) && !in_array('jpg', $extensions)) {
            $extensions[] = 'jpg';
        }

        return array_unique($extensions);
    }
}
