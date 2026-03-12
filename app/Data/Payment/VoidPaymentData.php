<?php

declare(strict_types=1);

namespace App\Data\Payment;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class VoidPaymentData extends Data
{
    public function __construct(
        public string $void_reason,
    ) {}

    //    public static function authorize(): bool
    //    {
    //        return true;
    //    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'void_reason' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array<string,string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'void_reason.required' => __('A reason is required to void a payment.'),
            'void_reason.max' => __('Void reason may not exceed 500 characters.'),
        ];
    }
}
