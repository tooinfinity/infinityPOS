<?php

declare(strict_types=1);

namespace App\Data\Purchase;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\CarbonInterface;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class PurchaseData extends Data
{
    public function __construct(
        public int $supplier_id,
        public int $warehouse_id,
        public PurchaseStatusEnum $status,
        public CarbonInterface $purchase_date,
        public int $total_amount,
        public int $paid_amount,
        public ?string $note,

        /** @var DataCollection<int, PurchaseItemData> */
        #[DataCollectionOf(PurchaseItemData::class)]
        public DataCollection $items,
    ) {}

    public static function fromModel(Purchase $model): self
    {
        return self::from([
            'supplier_id' => $model->supplier_id,
            'warehouse_id' => $model->warehouse_id,
            'status' => $model->status,
            'purchase_date' => $model->purchase_date,
            'total_amount' => $model->total_amount,
            'paid_amount' => $model->paid_amount,
            'note' => $model->note,
            'items' => $model->items->map(
                fn (PurchaseItem $item): array => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'expires_at' => $item->expires_at,
                ])->all(),
        ]);
    }

    //    public static function authorize(): bool
    //    {
    //        return true;
    //    }

    /**
     * @return array<string, array<int, Enum|string>>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'status' => ['required', Rule::enum(PurchaseStatusEnum::class)],
            'purchase_date' => ['required', 'date'],
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
            'supplier_id.required' => __('A supplier is required.'),
            'supplier_id.exists' => __('The selected supplier does not exist.'),
            'warehouse_id.required' => __('A warehouse is required.'),
            'warehouse_id.exists' => __('The selected warehouse does not exist.'),
            'purchase_date.required' => __('Purchase date is required.'),
            'items.required' => __('A purchase must have at least one item.'),
            'items.min' => __('A purchase must have at least one item.'),
            'total_amount.required' => __('Total amount is required.'),
            'paid_amount.required' => __('Paid amount is required.'),
        ];
    }
}
