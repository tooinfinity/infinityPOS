<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Data\Inventory\BulkStockAdjustmentData;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class BulkStockAdjustment
{
    public function __construct(
        private AdjustStock $adjustStock,
    ) {}

    /**
     * Perform multiple stock adjustments in a single transaction.
     *
     * @return Collection<int, StockMovement>
     *
     * @throws Throwable
     */
    public function handle(BulkStockAdjustmentData $data): Collection
    {
        return DB::transaction(function () use ($data): Collection {
            /** @var Collection<int, StockMovement> $movements */
            $movements = collect();

            foreach ($data->adjustments as $adjustment) {
                $movement = $this->adjustStock->handle($adjustment);
                $movements->push($movement);
            }

            return $movements;
        });
    }
}
