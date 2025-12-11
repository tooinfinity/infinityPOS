<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class PurchaseReturnItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $cost,
        public int $total,
        public ?string $batch_number,
        public Lazy|PurchaseReturnData $purchaseReturn,
        public Lazy|ProductData $product,
        public Lazy|PurchaseItemData|null $purchaseItem,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
