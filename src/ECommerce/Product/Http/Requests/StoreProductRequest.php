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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        // Convert 'details' from JSON string to array (for form-data requests)
        // if ($this->has('details') && is_string($this->details)) {
        //     $decoded = json_decode($this->details, true);
        //     if (json_last_error() === JSON_ERROR_NONE) {
        //         $data['details'] = $decoded;
        //     }
        // }

        // Convert 'is_active' from string to boolean
        if ($this->has('is_active')) {
            $data['is_active'] = filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        // Merge the converted data
        if (!empty($data)) {
            $this->merge($data);
        }
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
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['sometimes', 'nullable', 'string'],
            'minified_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'details' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'discount_type' => ['sometimes', 'nullable', 'in:percentage,fixed_amount'],
            'discount_value' => ['sometimes', 'nullable', 'numeric', 'min:0', 'required_with:discount_type'],
            'sku' => ['sometimes', 'nullable', 'string', 'max:100', 'unique:products,sku'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id'],
            'is_active' => ['sometimes', 'boolean'],
            'category_ids' => ['sometimes', 'nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'taxonomy_ids' => ['sometimes', 'nullable', 'array'],
            'taxonomy_ids.*' => ['integer', 'exists:taxonomies,id'],
            'bundle_id' => ['sometimes', 'nullable', 'integer', 'exists:bundles,id'],

            // Image upload fields
            'images' => ['sometimes', 'nullable', 'array'],
            'images.*' => [
                'required',
                'file',
                'image',
                'max:' . $maxSize,
                'mimes:' . implode(',', $allowedMimes),
            ],
            'image_alt_texts' => ['sometimes', 'nullable', 'array'],
            'image_alt_texts.*' => ['string', 'max:255'],
            'image_sort_orders' => ['sometimes', 'nullable', 'array'],
            'image_sort_orders.*' => ['integer', 'min:0'],
            'primary_image_index' => ['sometimes', 'integer', 'min:0'],
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
            'price.min' => 'Product price must be greater than or equal to 0.',
            'discount_type.in' => 'Discount type must be either percentage or fixed_amount.',
            'discount_value.required_with' => 'Discount value is required when discount type is set.',
            'discount_value.min' => 'Discount value must be greater than or equal to 0.',
            'sku.unique' => 'This SKU is already in use.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
            'taxonomy_ids.*.exists' => 'One or more selected taxonomies do not exist.',
            'images.*.file' => 'Each upload must be a valid file.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.max' => 'Each image must not exceed the maximum file size.',
            'images.*.mimes' => 'Each image must be a valid format (jpeg, png, webp, gif).',
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
            // add more if needed
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
