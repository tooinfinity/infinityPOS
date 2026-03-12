<?php

declare(strict_types=1);

namespace App\Data\Purchase;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class ReceivePurchaseData extends Data
{
    public function __construct(
        /** @var DataCollection<int, ReceivePurchaseItemData> */
        #[DataCollectionOf(ReceivePurchaseItemData::class)]
        public DataCollection $items,
    ) {}

    //    public static function authorize(): bool
    //    {
    //        return true;
    //    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'integer', 'exists:purchase_items,id'],
            'items.*.received_quantity' => ['required', 'integer', 'min:0'],
            'items.*.expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @param  array<string,string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'items.required' => __('At least one item is required.'),
            'items.*.purchase_item_id.required' => __('Each item must reference a valid purchase item.'),
            'items.*.received_quantity.required' => __('Received quantity is required for each item.'),
            'items.*.received_quantity.min' => __('Received quantity cannot be negative.'),
        ];
    }
}
