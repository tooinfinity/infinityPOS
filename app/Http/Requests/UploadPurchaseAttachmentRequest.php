<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

final class UploadPurchaseAttachmentRequest extends FormRequest
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
                File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx'])
                    ->max(20 * 1024), // 20MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'An attachment file is required.',
            'file.max' => 'The attachment must not exceed 20MB.',
            'file.mimes' => 'The attachment must be an image (JPG, PNG, WebP), PDF, or Word document (DOC, DOCX).',
        ];
    }
}
