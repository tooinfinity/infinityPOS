<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Exceptions\InvalidOperationException;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
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

    /**
     * @throws InvalidOperationException
     *
     * FIX: replaced 8 individual EXISTS queries with a single SQL query.
     * StockTransfer uses two FK columns (from_warehouse_id / to_warehouse_id)
     * so we check both in a single UNION subquery count.
     */
    private function ensureNoRelatedRecords(Warehouse $warehouse): void
    {
        $id = $warehouse->id;

        /** @var array<int, int> $result */
        $result = DB::selectOne(
            <<<'SQL'
            SELECT
                (SELECT COUNT(*) FROM batches          WHERE warehouse_id = ?)        AS batches,
                (SELECT COUNT(*) FROM stock_movements  WHERE warehouse_id = ?)        AS stockMovements,
                (SELECT COUNT(*) FROM purchases        WHERE warehouse_id = ?)        AS purchases,
                (SELECT COUNT(*) FROM sales            WHERE warehouse_id = ?)        AS sales,
                (SELECT COUNT(*) FROM stock_transfers  WHERE from_warehouse_id = ?
                                                       OR to_warehouse_id   = ?)      AS stockTransfers,
                (SELECT COUNT(*) FROM sale_returns     WHERE warehouse_id = ?)        AS saleReturns,
                (SELECT COUNT(*) FROM purchase_returns WHERE warehouse_id = ?)        AS purchaseReturns
            SQL,
            [$id, $id, $id, $id, $id, $id, $id, $id],
        );

        $existing = [];

        foreach ($result as $relation => $count) {
            if ($count > 0) {
                $existing[] = $relation;
            }
        }

        if ($existing !== []) {
            throw new InvalidOperationException(
                'delete',
                'Warehouse',
                sprintf('Cannot delete warehouse with existing %s', implode(', ', $existing))
            );
        }
    }
}
