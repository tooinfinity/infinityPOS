<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\PurchaseItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class PurchaseItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $cost,
        public ?int $discount,
        public ?int $tax_amount,
        public int $total,
        public ?string $batch_number,
        public ?CarbonInterface $expiry_date,
        public ?int $remaining_quantity,
        public Lazy|ProductData $product,
        /** @var Lazy|DataCollection<int|string, PurchaseReturnItemData> */
        public Lazy|DataCollection $returnItems,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(PurchaseItem $item): self
    {
        return new self(
            id: $item->id,
            quantity: $item->quantity,
            cost: $item->cost,
            discount: $item->discount,
            tax_amount: $item->tax_amount,
            total: $item->total,
            batch_number: $item->batch_number,
            expiry_date: $item->expiry_date,
            remaining_quantity: $item->remaining_quantity,
            product: Lazy::whenLoaded('product', $item, fn (): ProductData => ProductData::from($item->product)
            ),
            returnItems: Lazy::whenLoaded('returnItems', $item,
                /**
                 * @return Collection<int|string, PurchaseReturnItemData>
                 */
                fn (): Collection => PurchaseReturnItemData::collect($item->returnItems)
            ),
            created_at: $item->created_at,
            updated_at: $item->updated_at,
        );
    }
}
