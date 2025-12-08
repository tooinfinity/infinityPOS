<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\SaleReturnItem;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class SaleReturnItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $price,
        public int $cost,
        public ?int $discount,
        public ?int $tax_amount,
        public int $total,
        public Lazy|SaleReturnData $saleReturn,
        public Lazy|ProductData $product,
        public Lazy|SaleItemData|null $saleItem,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(SaleReturnItem $item): self
    {
        return new self(
            id: $item->id,
            quantity: $item->quantity,
            price: $item->price,
            cost: $item->cost,
            discount: $item->discount,
            tax_amount: $item->tax_amount,
            total: $item->total,
            saleReturn: Lazy::whenLoaded('saleReturn', $item, fn (): SaleReturnData => SaleReturnData::from($item->saleReturn)
            ),
            product: Lazy::whenLoaded('product', $item, fn (): ProductData => ProductData::from($item->product)
            ),
            saleItem: Lazy::whenLoaded('saleItem', $item, fn (): ?SaleItemData => $item->saleItem ? SaleItemData::from($item->saleItem) : null
            ),
            created_at: $item->created_at,
            updated_at: $item->updated_at,
        );
    }
}
