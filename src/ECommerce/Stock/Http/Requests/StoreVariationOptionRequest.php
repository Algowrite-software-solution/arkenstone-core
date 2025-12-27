<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVariationOptionRequest extends FormRequest
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
            'variant_id' => ['required', 'integer', 'exists:variants,id'],
            'name' => ['required', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
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
            'variant_id.required' => 'Variant is required.',
            'variant_id.exists' => 'The selected variant does not exist.',
            'name.required' => 'Option name is required.',
        ];
    }
}
