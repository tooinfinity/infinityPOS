<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class SaleItemData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('product_id')]
        public int $productId,

        #[Required, Min(1)]
        public int $quantity,

        #[Required, Min(0)]
        #[MapInputName('unit_price')]
        public int $unitPrice,

        #[Nullable, Min(0)]
        #[MapInputName('unit_cost')]
        public ?int $unitCost = null,
    ) {}

    public function subtotal(): int
    {
        return $this->quantity * $this->unitPrice;
    }

    public function profit(): int
    {
        if ($this->unitCost === null) {
            return 0;
        }

        return ($this->unitPrice - $this->unitCost) * $this->quantity;
    }
}
