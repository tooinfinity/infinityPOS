<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\SaleItem;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class SaleItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $price,
        public int $cost,
        public ?int $discount,
        public ?int $tax_amount,
        public int $total,
        public ?string $batch_number,
        public ?CarbonInterface $expiry_date,
        public Lazy|ProductData $product,
        /** @var Lazy|DataCollection<SaleReturnItemData> */
        public Lazy|DataCollection $returnItems,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(SaleItem $item): self
    {
        return new self(
            id: $item->id,
            quantity: $item->quantity,
            price: $item->price,
            cost: $item->cost,
            discount: $item->discount,
            tax_amount: $item->tax_amount,
            total: $item->total,
            batch_number: $item->batch_number,
            expiry_date: $item->expiry_date,
            product: Lazy::whenLoaded('product', $item, fn (): ProductData => ProductData::from($item->product)
            ),
            returnItems: Lazy::whenLoaded('returnItems', $item, fn (): DataCollection => SaleReturnItemData::collect($item->returnItems)),
            created_at: $item->created_at,
            updated_at: $item->updated_at,
        );
    }
}
