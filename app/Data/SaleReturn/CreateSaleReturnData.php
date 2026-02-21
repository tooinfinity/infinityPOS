<?php

declare(strict_types=1);

namespace App\Data\SaleReturn;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class CreateSaleReturnData extends Data
{
    /**
     * @param  DataCollection<int, SaleReturnItemData>  $items
     */
    public function __construct(
        public int $sale_id,
        public int $warehouse_id,
        public ?int $user_id,
        public DateTimeInterface|string $return_date,
        public ?string $note,
        public DataCollection $items,
    ) {}
}
