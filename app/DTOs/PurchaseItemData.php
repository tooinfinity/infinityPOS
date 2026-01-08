<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class PurchaseItemData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('product_id')]
        public int $productId,

        #[Required, Min(1)]
        public int $quantity,

        #[Required, Min(0)]
        #[MapInputName('unit_cost')]
        public int $unitCost,

        #[Nullable, Date]
        #[MapInputName('expiry_date')]
        public ?string $expiryDate = null,

        #[Nullable, Max(100)]
        #[MapInputName('batch_number')]
        public ?string $batchNumber = null,
    ) {}

    public function subtotal(): int
    {
        return $this->quantity * $this->unitCost;
    }
}
