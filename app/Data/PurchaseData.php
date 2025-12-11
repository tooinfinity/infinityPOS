<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class PurchaseData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public int $subtotal,
        public int $discount,
        public int $tax,
        public int $total,
        public int $paid,
        public string $status,
        public ?string $notes,
        public Lazy|SupplierData|null $supplier,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
