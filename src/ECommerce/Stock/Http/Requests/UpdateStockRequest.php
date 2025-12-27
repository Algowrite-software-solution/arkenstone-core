<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Requests;

use Arkenstone\Core\ECommerce\Stock\Enum\StockStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
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
        $stockId = $this->route('id') ?? $this->route('stock');

        return [
            'product_id' => ['sometimes', 'integer', 'exists:products,id'],
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('stocks', 'sku')->ignore($stockId)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'quantity_on_hand' => ['sometimes', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'supplier_id' => ['sometimes', 'integer', 'exists:suppliers,id'],
            'image_url_id' => ['nullable', 'integer', 'exists:product_images,id'],
            'status' => ['nullable', 'string', StockStatus::validationRule()],
            'variation_option_ids' => ['nullable', 'array'],
            'variation_option_ids.*' => ['integer', 'exists:variation_options,id'],
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
            'product_id.exists' => 'The selected product does not exist.',
            'sku.unique' => 'This SKU is already in use.',
            'price.min' => 'Price must be greater than or equal to 0.',
            'quantity_on_hand.min' => 'Quantity must be greater than or equal to 0.',
            'supplier_id.exists' => 'The selected supplier does not exist.',
            'status.in' => 'Status must be either active or inactive.',
            'variation_option_ids.*.exists' => 'One or more selected variation options do not exist.',
        ];
    }
}
