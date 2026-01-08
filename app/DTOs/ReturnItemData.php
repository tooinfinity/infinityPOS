<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class ReturnItemData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('product_id')]
        public int $productId,

        #[Required, Min(1)]
        public int $quantity,

        #[Nullable, Min(0)]
        public int $subtotal = 0,

        #[Nullable]
        #[MapInputName('return_id')]
        public ?int $returnId = null,

        #[Nullable]
        #[MapInputName('sale_item_id')]
        public ?int $saleItemId = null,

        #[Nullable]
        #[MapInputName('invoice_item_id')]
        public ?int $invoiceItemId = null,

        #[Nullable, Min(0)]
        #[MapInputName('unit_price')]
        public ?int $unitPrice = null,

        #[Nullable, Min(0)]
        #[MapInputName('unit_cost')]
        public ?int $unitCost = null,
    ) {}

    public function subtotal(): int
    {
        return $this->quantity * ($this->unitPrice ?? 0);
    }

    public function getSubtotal(): int
    {
        return $this->subtotal();
    }

    public function calculateSubtotal(): int
    {
        return $this->quantity * ($this->unitPrice ?? 0);
    }

    public function refundAmount(): int
    {
        return $this->subtotal();
    }
}
