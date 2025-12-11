<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class StockTransferData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public string $status,
        public ?string $notes,
        public Lazy|StoreData $fromStore,
        public Lazy|StoreData $toStore,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
