<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Products\ProductData;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class PurchaseReturnItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $cost,
        public int $total,
        public ?string $batch_number,
        public Lazy|PurchaseReturnData|null $purchaseReturn,
        public Lazy|ProductData|null $product,
        public Lazy|PurchaseItemData|null $purchaseItem,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
