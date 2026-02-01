<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:brands,slug'],
            'description' => ['nullable', 'string'],
            'logo_image' => ['nullable', 'sometimes', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,svg'],
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
            'name.required' => 'Brand name is required.',
            'slug.unique' => 'This slug is already in use.',
            'logo_image.max' => 'Logo image must not exceed 2MB.',
            'logo_image.mimes' => 'Logo image must be a jpeg, png, jpg, gif or svg file.',
            'logo_image.image' => 'Logo image must be a valid image file.',
        ];
    }
}
