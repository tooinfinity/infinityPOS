<?php

declare(strict_types=1);

namespace App\Data\StockTransfer;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class CreateStockTransferData extends Data
{
    /**
     * @param  DataCollection<int, StockTransferItemData>  $items
     */
    public function __construct(
        public int $from_warehouse_id,
        public int $to_warehouse_id,
        public ?string $note,
        public DateTimeInterface|string $transfer_date,
        public ?int $user_id,
        public DataCollection $items,
    ) {}
}
