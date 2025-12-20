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
final class StockTransferItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public ?string $batch_number,
        public Lazy|StockTransferData|null $stockTransfer,
        public Lazy|ProductData|null $product,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
