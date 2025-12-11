<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\PurchaseItem;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
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
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $expiry_date,
        public ?int $remaining_quantity,
        #[Lazy] public ?ProductData $product,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
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
            expiry_date: $item->expiry_date?->toDayDateTimeString(),
            remaining_quantity: $item->remaining_quantity,
            product: $item->product ? ProductData::from($item->product) : null,
            created_at: $item->created_at?->toDayDateTimeString(),
            updated_at: $item->updated_at?->toDayDateTimeString(),
        );
    }
}
