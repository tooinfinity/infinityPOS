<?php

declare(strict_types=1);

namespace App\Data\StockTransfer;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class StockTransferData extends Data
{
    public function __construct(
        public int $from_warehouse_id,
        public int $to_warehouse_id,
        public CarbonInterface $transfer_date,
        public ?string $note,

        /** @var DataCollection<int, StockTransferItemData> */
        #[DataCollectionOf(StockTransferItemData::class)]
        public DataCollection $items,
    ) {}

    public static function fromModel(StockTransfer $model): self
    {
        return self::from([
            'from_warehouse_id' => $model->from_warehouse_id,
            'to_warehouse_id' => $model->to_warehouse_id,
            'transfer_date' => $model->transfer_date,
            'note' => $model->note,
            'items' => $model->items->map(
                fn (StockTransferItem $item): array => [
                    'product_id' => $item->product_id,
                    'batch_id' => $item->batch_id,
                    'quantity' => $item->quantity,
                ])->all(),
        ]);
    }

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
            'from_warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'to_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                'different:from_warehouse_id',
            ],
            'transfer_date' => ['required', 'date'],
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
            'from_warehouse_id.required' => __('Source warehouse is required.'),
            'from_warehouse_id.exists' => __('The selected source warehouse does not exist.'),
            'to_warehouse_id.required' => __('Destination warehouse is required.'),
            'to_warehouse_id.exists' => __('The selected destination warehouse does not exist.'),
            'to_warehouse_id.different' => __('Source and destination warehouse cannot be the same.'),
            'transfer_date.required' => __('Transfer date is required.'),
            'items.required' => __('A transfer must have at least one item.'),
            'items.min' => __('A transfer must have at least one item.'),
        ];
    }
}
