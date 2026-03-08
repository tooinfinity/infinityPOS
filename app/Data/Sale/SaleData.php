<?php

declare(strict_types=1);

namespace App\Data\Sale;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Carbon\CarbonInterface;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class SaleData extends Data
{
    public function __construct(
        public ?int $customer_id,
        public int $warehouse_id,
        public SaleStatusEnum $status,
        public CarbonInterface $sale_date,
        public int $total_amount,
        public int $paid_amount,
        public ?string $note,

        /** @var DataCollection<int, SaleItemData> */
        #[DataCollectionOf(SaleItemData::class)]
        public DataCollection $items,
    ) {}

    public static function fromModel(Sale $model): self
    {
        return self::from([
            'customer_id' => $model->customer_id,
            'warehouse_id' => $model->warehouse_id,
            'status' => $model->status,
            'sale_date' => $model->sale_date,
            'total_amount' => $model->total_amount,
            'paid_amount' => $model->paid_amount,
            'note' => $model->note,
            'items' => $model->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'unit_cost' => $item->unit_cost,
            ])->toArray(),
        ]);
    }

    public static function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, Enum|string>>
     *
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'status' => ['required', Rule::enum(SaleStatusEnum::class)],
            'sale_date' => ['required', 'date'],
            'total_amount' => ['required', 'integer', 'min:0'],
            'paid_amount' => ['required', 'integer', 'min:0'],
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
            'warehouse_id.required' => __('A warehouse is required.'),
            'warehouse_id.exists' => __('The selected warehouse does not exist.'),
            'items.required' => __('A sale must have at least one item.'),
            'items.min' => __('A sale must have at least one item.'),
            'total_amount.required' => __('Total amount is required.'),
            'paid_amount.required' => __('Paid amount is required.'),
        ];
    }
}
