<?php

declare(strict_types=1);

namespace App\Data\Payment;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class PaymentData extends Data
{
    public function __construct(
        #[IntegerType, Exists('payment_methods', 'id')]
        public int $payment_method_id,

        #[IntegerType, Min(1)]
        public int $amount,

        public string $payment_date,

        #[Nullable]
        public ?string $note,
    ) {}

    public static function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array<string,string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'payment_method_id.required' => __('A payment method is required.'),
            'payment_method_id.exists' => __('The selected payment method does not exist.'),
            'amount.required' => __('Payment amount is required.'),
            'amount.min' => __('Payment amount must be at least 1.'),
            'payment_date.required' => __('Payment date is required.'),
        ];
    }
}
