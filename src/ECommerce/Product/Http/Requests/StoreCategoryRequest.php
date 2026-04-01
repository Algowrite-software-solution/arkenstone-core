<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
        $config = config('arkenstone.category_images') ?? [];
        $maxSize = $config['max_size'] ?? 5120;
        $allowedMimes = $this->extractMimeExtensions($config['allowed_types'] ?? ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
        $mimeString = implode(',', $allowedMimes);

        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->hasFile('image')) {
            if (is_array($this->file('image'))) {
                $rules['image'] = ['sometimes', 'array'];
                $rules['image.*'] = ['file', 'image', 'max:' . $maxSize, 'mimes:' . $mimeString];
            } else {
                $rules['image'] = ['sometimes', 'file', 'image', 'max:' . $maxSize, 'mimes:' . $mimeString];
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'slug.unique' => 'This slug is already in use.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'image.file' => 'The upload must be a valid file.',
            'image.image' => 'The file must be a valid image.',
            'image.max' => 'The image must not exceed the maximum file size.',
            'image.mimes' => 'The image must be a valid format.',
            'image.*.file' => 'Each upload must be a valid file.',
            'image.*.image' => 'Each file must be a valid image.',
            'image.*.max' => 'Each image must not exceed the maximum file size.',
            'image.*.mimes' => 'Each image must be a valid format.',
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
