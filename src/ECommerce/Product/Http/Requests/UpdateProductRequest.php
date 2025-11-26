<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product') ?? $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $productId],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:percentage,fixed_amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'required_with:discount_type'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,' . $productId],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'taxonomy_ids' => ['nullable', 'array'],
            'taxonomy_ids.*' => ['integer', 'exists:taxonomies,id'],
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
            'price.required' => 'Product price is required.',
            'price.min' => 'Product price must be greater than or equal to 0.',
            'discount_type.in' => 'Discount type must be either percentage or fixed_amount.',
            'discount_value.required_with' => 'Discount value is required when discount type is set.',
            'discount_value.min' => 'Discount value must be greater than or equal to 0.',
            'sku.unique' => 'This SKU is already in use.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
            'taxonomy_ids.*.exists' => 'One or more selected taxonomies do not exist.',
        ];
    }
}
