<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\PurchaseReturn;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class PurchaseReturnData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public int $total,
        public int $refunded,
        public string $status,
        public ?string $reason,
        public ?string $notes,
        #[Lazy] public ?PurchaseData $purchase,
        #[Lazy] public ?SupplierData $supplier,
        #[Lazy] public ?StoreData $store,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(PurchaseReturn $return): self
    {
        return new self(
            id: $return->id,
            reference: $return->reference,
            total: $return->total,
            refunded: $return->refunded,
            status: $return->status,
            reason: $return->reason,
            notes: $return->notes,
            purchase: $return->purchase ? PurchaseData::from($return->purchase) : null,
            supplier: $return->supplier ? SupplierData::from($return->supplier) : null,
            store: $return->store ? StoreData::from($return->store) : null,
            creator: $return->creator ? UserData::from($return->creator) : null,
            updater: $return->updater ? UserData::from($return->updater) : null,
            created_at: $return->created_at?->toDateTimeString(),
            updated_at: $return->updated_at?->toDateTimeString(),
        );
    }
}
