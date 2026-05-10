<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Requests;

use Arkenstone\Core\ECommerce\Stock\Enum\ReferenceType;
use Illuminate\Foundation\Http\FormRequest;

class ReserveStockRequest extends FormRequest
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
        return [
            'stock_id' => ['required', 'integer', 'exists:stocks,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference_type' => ['required', 'string', ReferenceType::validationRule()],
            'reference_id' => ['required', 'string'],
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
            'stock_id.required' => 'Stock ID is required.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'reference_type.required' => 'Reference type is required.',
            'reference_type.in' => 'Reference type must be either cart or order.',
            'reference_id.required' => 'Reference ID is required.',
        ];
    }
}
