<?php

declare(strict_types=1);

namespace App\Http\Requests\Batch;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'batch_number' => ['nullable', 'string', 'max:50'],
            'cost_amount' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
