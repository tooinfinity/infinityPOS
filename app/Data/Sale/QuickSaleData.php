<?php

declare(strict_types=1);

namespace App\Data\Sale;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class QuickSaleData extends Data
{
    /**
     * @param  DataCollection<int, SaleItemData>  $items
     */
    public function __construct(
        public int $customer_id,
        public int $warehouse_id,
        public ?int $user_id,
        public int $payment_method_id,
        public DateTimeInterface|string $sale_date,
        public int $paid_amount,
        public ?string $note,
        public DataCollection $items,
    ) {}
}
