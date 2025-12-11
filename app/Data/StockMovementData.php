<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use App\\Enums\\StockMovementTypeEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class StockMovementData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public StockMovementTypeEnum $type,
        public ?string $reference,
        public ?string $batch_number,
        public ?string $notes,
        public Lazy|ProductData $product,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
