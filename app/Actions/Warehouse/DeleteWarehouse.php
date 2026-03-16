<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Exceptions\InvalidOperationException;
use App\Models\Warehouse;
use Throwable;

final readonly class DeleteWarehouse
{
    /**
     * @throws Throwable
     */
    public function handle(Warehouse $warehouse): bool
    {
        return $warehouse->getConnection()->transaction(function () use ($warehouse): bool {
            $this->ensureNoRelatedRecords($warehouse);

            return (bool) $warehouse->delete();
        });
    }

    /**
     * @throws InvalidOperationException
     */
    private function ensureNoRelatedRecords(Warehouse $warehouse): void
    {
        $existing = array_filter([
            'batches' => $warehouse->batches()->exists(),
            'stockMovements' => $warehouse->stockMovements()->exists(),
            'purchases' => $warehouse->purchases()->exists(),
            'sales' => $warehouse->sales()->exists(),
            'transfersFrom' => $warehouse->transfersFrom()->exists(),
            'transfersTo' => $warehouse->transfersTo()->exists(),
            'saleReturns' => $warehouse->saleReturns()->exists(),
            'purchaseReturns' => $warehouse->purchaseReturns()->exists(),
        ]);

        if ($existing !== []) {
            throw new InvalidOperationException(
                'delete',
                'Warehouse',
                sprintf('Cannot delete warehouse with existing %s', implode(', ', array_keys($existing)))
            );
        }
    }
}
