<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\RefundMethodEnum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class ReturnData extends Data
{
    /**
     * @param  array<int, ReturnItemData>  $items
     */
    public function __construct(
        #[Required]
        #[MapInputName('store_id')]
        public int $storeId,

        #[Nullable]
        #[MapInputName('sale_id')]
        public ?int $saleId,

        #[Nullable]
        #[MapInputName('invoice_id')]
        public ?int $invoiceId,

        #[Required]
        #[MapInputName('return_date')]
        public string $returnDate,

        #[Required, Min(0)]
        #[MapInputName('total_amount')]
        public int $totalAmount,

        #[Required]
        #[MapInputName('refund_method')]
        public RefundMethodEnum $refundMethod,

        #[DataCollectionOf(ReturnItemData::class)]
        public array $items = [],

        #[Nullable]
        public ?string $reason = null,

        #[Nullable]
        #[MapInputName('processed_by')]
        public ?int $processedBy = null,
    ) {}

    public function calculateTotal(): int
    {
        return array_reduce(
            $this->items,
            fn (int $carry, ReturnItemData $item): int => $carry + $item->subtotal,
            0
        );
    }
}
