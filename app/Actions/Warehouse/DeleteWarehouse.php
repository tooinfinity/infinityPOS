<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class DeleteWarehouse
{
    /**
     * @throws Throwable
     */
    public function handle(Warehouse $warehouse): bool
    {
        return DB::transaction(function () use ($warehouse): bool {
            $this->ensureNoRelatedRecords($warehouse);

            return (bool) $warehouse->delete();
        });
    }

    private function ensureNoRelatedRecords(Warehouse $warehouse): void
    {
        $relations = [
            'batches' => $warehouse->batches()->exists(),
            'stockMovements' => $warehouse->stockMovements()->exists(),
            'purchases' => $warehouse->purchases()->exists(),
            'sales' => $warehouse->sales()->exists(),
            'transfersFrom' => $warehouse->transfersFrom()->exists(),
            'transfersTo' => $warehouse->transfersTo()->exists(),
            'saleReturns' => $warehouse->saleReturns()->exists(),
            'purchaseReturns' => $warehouse->purchaseReturns()->exists(),
        ];

        $existingRelations = array_keys(array_filter($relations));

        if ($existingRelations !== []) {
            throw new RuntimeException(
                sprintf(
                    'Cannot delete warehouse with existing %s',
                    implode(', ', $existingRelations)
                )
            );
        }
    }
}
