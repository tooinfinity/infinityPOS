<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class InvoiceItemData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('invoice_id')]
        public int $invoiceId,

        #[Required]
        #[MapInputName('product_id')]
        public int $productId,

        #[Required, Min(1)]
        public int $quantity,

        #[Required, Min(0)]
        #[MapInputName('unit_price')]
        public int $unitPrice,

        #[Required, Min(0)]
        #[MapInputName('unit_cost')]
        public int $unitCost,
    ) {}

    public function subtotal(): int
    {
        return $this->quantity * $this->unitPrice;
    }

    public function profit(): int
    {
        return ($this->unitPrice - $this->unitCost) * $this->quantity;
    }
}
