<?php

declare(strict_types=1);

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if (! $this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:100', 'unique:payment_methods,name'],
            'code' => ['required', 'string', 'min:1', 'max:50', 'unique:payment_methods,code'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
