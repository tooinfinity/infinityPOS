<?php

declare(strict_types=1);

namespace App\Http\Requests\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePaymentMethodRequest extends FormRequest
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
        $paymentMethod = $this->route('paymentMethod');
        $paymentMethodId = $paymentMethod instanceof PaymentMethod ? $paymentMethod->id : null;

        return [
            'name' => ['required', 'string', 'min:1', 'max:100', Rule::unique('payment_methods', 'name')->ignore($paymentMethodId)],
            'code' => ['required', 'string', 'min:1', 'max:50', Rule::unique('payment_methods', 'code')->ignore($paymentMethodId)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
