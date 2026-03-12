<?php

declare(strict_types=1);

namespace App\Data\PurchaseReturn;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class PurchaseReturnData extends Data
{
    public function __construct(
        public int $purchase_id,
        public int $warehouse_id,
        public string $return_date,
        public ?string $note,

        /** @var DataCollection<int, PurchaseReturnItemData> */
        #[DataCollectionOf(PurchaseReturnItemData::class)]
        public DataCollection $items,
    ) {}

    public static function fromModel(PurchaseReturn $model): self
    {
        return self::from([
            'purchase_id' => $model->purchase_id,
            'warehouse_id' => $model->warehouse_id,
            'return_date' => $model->return_date,
            'note' => $model->note,
            'items' => $model->items->map(
                fn (PurchaseReturnItem $item): array => [
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                ]
            )->all(),
        ]);
    }

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
            'purchase_id' => ['required', 'integer', 'exists:purchases,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'return_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
        ];
    }

    /**
     * @param  array<string,string>  ...$args
     * @return array<string, string>
     */
    public static function messages(...$args): array
    {
        return [
            'purchase_id.required' => __('A purchase reference is required.'),
            'purchase_id.exists' => __('The selected purchase does not exist.'),
            'warehouse_id.required' => __('A warehouse is required.'),
            'warehouse_id.exists' => __('The selected warehouse does not exist.'),
            'return_date.required' => __('Return date is required.'),
            'items.required' => __('A return must have at least one item.'),
            'items.min' => __('A return must have at least one item.'),
        ];
    }
}
