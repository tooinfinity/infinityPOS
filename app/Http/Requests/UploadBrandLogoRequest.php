<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

final class UploadBrandLogoRequest extends FormRequest
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
            'logo' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(2 * 1024), // 2MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'A logo file is required.',
            'logo.max' => 'The logo must not exceed 2MB.',
            'logo.mimes' => 'The logo must be a JPG, PNG, or WebP image.',
        ];
    }
}
