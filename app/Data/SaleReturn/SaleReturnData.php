<?php

declare(strict_types=1);

namespace App\Data\SaleReturn;

use App\Models\SaleReturn;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class SaleReturnData extends Data
{
    public function __construct(
        public int $sale_id,
        public int $warehouse_id,
        public CarbonInterface $return_date,
        public ?string $note,

        /** @var DataCollection<int, SaleReturnItemData> */
        #[DataCollectionOf(SaleReturnItemData::class)]
        public DataCollection $items,
    ) {}

    public static function fromModel(SaleReturn $model): self
    {
        return self::from([
            'sale_id' => $model->sale_id,
            'warehouse_id' => $model->warehouse_id,
            'return_date' => $model->return_date,
            'note' => $model->note,
            'items' => $model->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
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
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
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
            'sale_id.required' => __('A sale reference is required.'),
            'sale_id.exists' => __('The selected sale does not exist.'),
            'warehouse_id.required' => __('A warehouse is required.'),
            'warehouse_id.exists' => __('The selected warehouse does not exist.'),
            'return_date.required' => __('Return date is required.'),
            'items.required' => __('A return must have at least one item.'),
            'items.min' => __('A return must have at least one item.'),
        ];
    }
}
