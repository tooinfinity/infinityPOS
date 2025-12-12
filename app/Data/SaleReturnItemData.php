<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
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
        public Lazy|SaleReturnData|null $saleReturn,
        public Lazy|ProductData|null $product,
        public Lazy|SaleItemData|null $saleItem,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
