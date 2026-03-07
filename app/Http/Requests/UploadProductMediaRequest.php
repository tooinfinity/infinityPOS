<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

final class UploadProductMediaRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(5 * 1024), // 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'A thumbnail image is required.',
            'file.max' => 'The thumbnail must not exceed 5MB.',
            'file.mimes' => 'The thumbnail must be a JPG, PNG, or WebP image.',
        ];
    }
}
