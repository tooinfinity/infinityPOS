<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteBatch
{
    /**
     * @throws Throwable
     */
    public function handle(Batch $batch): bool
    {
        return DB::transaction(function () use ($batch): bool {
            $this->ensureNoRelatedRecords($batch);

            return (bool) $batch->delete();
        });
    }

    /**
     * @throws InvalidOperationException
     *
     * FIX: replaced 6 individual EXISTS queries with a single SQL query that
     * checks all relations at once. The previous code fired 6 round-trips per
     * delete attempt; this fires 1.
     */
    private function ensureNoRelatedRecords(Batch $batch): void
    {
        /** @var array<int, int> $result */
        $result = DB::selectOne(
            <<<'SQL'
            SELECT
                (SELECT COUNT(*) FROM stock_movements        WHERE batch_id = ?) AS stock_movements,
                (SELECT COUNT(*) FROM purchase_items         WHERE batch_id = ?) AS purchase_items,
                (SELECT COUNT(*) FROM sale_items             WHERE batch_id = ?) AS sale_items,
                (SELECT COUNT(*) FROM stock_transfer_items   WHERE batch_id = ?) AS stock_transfer_items,
                (SELECT COUNT(*) FROM sale_return_items      WHERE batch_id = ?) AS sale_return_items,
                (SELECT COUNT(*) FROM purchase_return_items  WHERE batch_id = ?) AS purchase_return_items
            SQL,
            array_fill(0, 6, $batch->id),
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
                'Batch',
                sprintf('Cannot delete batch with existing %s', implode(', ', $existing))
            );
        }
    }
}
