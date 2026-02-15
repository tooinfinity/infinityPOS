<?php

declare(strict_types=1);

namespace App\Data\StockMovement;

use App\Enums\StockMovementTypeEnum;
use DateTimeInterface;
use Spatie\LaravelData\Data;

final class RecordStockMovementData extends Data
{
    public function __construct(
        public int $warehouse_id,
        public int $product_id,
        public StockMovementTypeEnum $type,
        public int $quantity,
        public int $previous_quantity,
        public int $current_quantity,
        public string $reference_type,
        public int $reference_id,
        public ?int $batch_id,
        public ?int $user_id,
        public ?string $note,
        public DateTimeInterface|string|null $created_at,
    ) {}
}
