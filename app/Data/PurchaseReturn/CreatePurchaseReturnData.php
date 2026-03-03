<?php

declare(strict_types=1);

namespace App\Data\PurchaseReturn;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class CreatePurchaseReturnData extends Data
{
    /**
     * @param  DataCollection<int, PurchaseReturnItemData>  $items
     */
    public function __construct(
        public int $purchase_id,
        public int $warehouse_id,
        public ?int $user_id,
        public DateTimeInterface|string $return_date,
        public ?string $note,
        public DataCollection $items,
    ) {}
}
