<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Purchase;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class PurchaseData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public int $paid,
        public string $status,
        public ?string $notes,
        #[Lazy] public ?SupplierData $supplier,
        #[Lazy] public ?StoreData $store,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Purchase $purchase): self
    {
        return new self(
            id: $purchase->id,
            reference: $purchase->reference,
            subtotal: $purchase->subtotal,
            discount: $purchase->discount,
            tax: $purchase->tax,
            total: $purchase->total,
            paid: $purchase->paid,
            status: $purchase->status,
            notes: $purchase->notes,
            supplier: $purchase->supplier ? SupplierData::from($purchase->supplier) : null,
            store: $purchase->store ? StoreData::from($purchase->store) : null,
            creator: $purchase->creator ? UserData::from($purchase->creator) : null,
            updater: $purchase->updater ? UserData::from($purchase->updater) : null,
            created_at: $purchase->created_at,
            updated_at: $purchase->updated_at,
        );
    }
}
