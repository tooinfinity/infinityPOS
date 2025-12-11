<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
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
        public Lazy|PurchaseData|null $purchase,
        public Lazy|SupplierData|null $supplier,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
