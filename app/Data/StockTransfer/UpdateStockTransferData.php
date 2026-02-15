<?php

declare(strict_types=1);

namespace App\Data\StockTransfer;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateStockTransferData extends Data
{
    public function __construct(
        public string|Optional|null $note,
        public DateTimeInterface|string|Optional $transfer_date,
        public int|Optional|null $user_id,
    ) {}
}
