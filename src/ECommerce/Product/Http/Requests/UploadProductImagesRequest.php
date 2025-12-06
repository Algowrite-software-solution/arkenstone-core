<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImagesRequest extends FormRequest
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
        $maxSize = $config['max_size'] ?? 5120; // KB
        $allowedMimes = $this->extractMimeExtensions($config['allowed_types'] ?? []);

        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*' => [
                'required',
                'file',
                'image',
                'max:' . $maxSize,
                'mimes:' . implode(',', $allowedMimes),
            ],
            'alt_texts' => ['nullable', 'array'],
            'alt_texts.*' => ['nullable', 'string', 'max:255'],
            'sort_orders' => ['nullable', 'array'],
            'sort_orders.*' => ['nullable', 'integer', 'min:0'],
            'primary_index' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $config = config('arkenstone.product_images');
        $maxSize = $config['max_size'] ?? 5120;
        $maxSizeMB = round($maxSize / 1024, 2);

        return [
            'images.required' => 'At least one image is required.',
            'images.array' => 'Images must be provided as an array.',
            'images.min' => 'At least one image must be uploaded.',
            'images.*.required' => 'Each image file is required.',
            'images.*.file' => 'Each upload must be a valid file.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.max' => "Each image must not exceed {$maxSizeMB}MB.",
            'images.*.mimes' => 'Each image must be a valid format (jpeg, png, webp, gif).',
            'alt_texts.*.max' => 'Alt text must not exceed 255 characters.',
            'sort_orders.*.min' => 'Sort order must be greater than or equal to 0.',
            'primary_index.integer' => 'Primary index must be an integer.',
            'primary_index.min' => 'Primary index must be greater than or equal to 0.',
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

        // Add jpg if jpeg is present
        if (in_array('jpeg', $extensions) && !in_array('jpg', $extensions)) {
            $extensions[] = 'jpg';
        }

        return array_unique($extensions);
    }
}
