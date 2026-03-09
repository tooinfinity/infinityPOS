<?php

declare(strict_types=1);

namespace App\Data\Payment;

use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class PaymentMethodData extends Data
{
    public function __construct(
        public string $name,
        public string $code,
        public bool $is_active,
    ) {}

    public static function fromModel(PaymentMethod $model): self
    {
        return self::from([
            'name' => $model->name,
            'code' => $model->code,
            'is_active' => $model->is_active,
        ]);
    }

    public static function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, Unique|string>>
     */
    public static function rules(ValidationContext $context): array
    {
        $routeParam = request()->route('paymentMethod');

        $methodId = $routeParam instanceof PaymentMethod
            ? $routeParam->id
            : null;

        return [
            'name' => ['required', 'string', 'max:80',
                Rule::unique('payment_methods', 'name')->ignore($methodId),
            ],
            'code' => ['required', 'string', 'max:20',
                Rule::unique('payment_methods', 'code')->ignore($methodId),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @param  array<string, string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'name.required' => __('Payment method name is required.'),
            'name.unique' => __('This payment method name is already taken.'),
            'code.required' => __('Payment method code is required.'),
            'code.unique' => __('This payment method code is already taken.'),
        ];
    }
}
