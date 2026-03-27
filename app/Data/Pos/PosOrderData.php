<?php

declare(strict_types=1);

namespace App\Data\Pos;

use App\Rules\EnsureValidPosCartInventory;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class PosOrderData extends Data
{
    /**
     * @param  DataCollection<int, PosCartItemData>  $items
     */
    public function __construct(
        #[Nullable, IntegerType, Exists('customers', 'id')]
        public ?int $customer_id,

        #[IntegerType, Exists('warehouses', 'id')]
        public int $warehouse_id,

        #[IntegerType, Exists('payment_methods', 'id')]
        public int $payment_method_id,

        #[IntegerType, Min(0)]
        public int $cash_tendered,

        #[IntegerType, Min(0)]
        public int $total_amount,

        #[Nullable]
        public ?string $note,

        public DataCollection $items,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'cash_tendered' => ['required', 'integer', 'min:0'],
            'total_amount' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],

            'items' => [
                'required',
                'array',
                'min:1',
                new EnsureValidPosCartInventory(),
            ],
        ];
    }

    /**
     * @param  array<string,string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'warehouse_id.required' => __('A warehouse is required.'),
            'warehouse_id.exists' => __('The selected warehouse does not exist.'),
            'payment_method_id.required' => __('A payment method is required.'),
            'payment_method_id.exists' => __('The selected payment method does not exist.'),
            'cash_tendered.required' => __('Cash tendered amount is required.'),
            'cash_tendered.min' => __('Cash tendered cannot be negative.'),
            'total_amount.required' => __('Total amount is required.'),
            'total_amount.min' => __('Total amount must be at least 1.'),
            'items.required' => __('Cart cannot be empty.'),
            'items.min' => __('Cart cannot be empty.'),
        ];
    }
}
