<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetachTaxonomiesRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'taxonomy_ids' => ['required', 'array', 'min:1'],
            'taxonomy_ids.*' => ['required', 'integer', 'exists:taxonomies,id'],
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
            'product_id.required' => 'Product ID is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'taxonomy_ids.required' => 'At least one taxonomy must be provided to detach.',
            'taxonomy_ids.array' => 'Taxonomy IDs must be an array.',
            'taxonomy_ids.min' => 'At least one taxonomy must be provided to detach.',
            'taxonomy_ids.*.required' => 'Each taxonomy ID is required.',
            'taxonomy_ids.*.integer' => 'Each taxonomy ID must be an integer.',
            'taxonomy_ids.*.exists' => 'One or more selected taxonomies do not exist.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'taxonomy_ids' => 'taxonomies',
            'taxonomy_ids.*' => 'taxonomy',
        ];
    }
}
