<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxonomyRequest extends FormRequest
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
        $taxonomyId = $this->route('taxonomy') ?? $this->route('id');

        return [
            'taxonomy_type_id' => ['sometimes', 'required', 'integer', 'exists:taxonomy_types,id'],
            'parent_id' => ['nullable', 'integer', 'exists:taxonomies,id', 'not_in:' . $taxonomyId],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:taxonomies,slug,' . $taxonomyId],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
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
            'taxonomy_type_id.required' => 'Taxonomy type is required.',
            'taxonomy_type_id.exists' => 'The selected taxonomy type does not exist.',
            'name.required' => 'Taxonomy name is required.',
            'slug.unique' => 'This slug is already in use.',
            'parent_id.exists' => 'The selected parent taxonomy does not exist.',
            'parent_id.not_in' => 'A taxonomy cannot be its own parent.',
            'sort_order.min' => 'Sort order must be greater than or equal to 0.',
        ];
    }
}
