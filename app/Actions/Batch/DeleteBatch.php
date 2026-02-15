<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use RuntimeException;
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

    private function ensureNoRelatedRecords(Batch $batch): void
    {
        $relations = [
            'stockMovements' => $batch->stockMovements()->exists(),
            'purchaseItems' => $batch->purchaseItems()->exists(),
            'saleItems' => $batch->saleItems()->exists(),
            'stockTransferItems' => $batch->stockTransferItems()->exists(),
            'saleReturnItems' => $batch->saleReturnItems()->exists(),
            'purchaseReturnItems' => $batch->purchaseReturnItems()->exists(),
        ];

        $existingRelations = array_keys(array_filter($relations));

        if ($existingRelations !== []) {
            throw new RuntimeException(
                sprintf(
                    'Cannot delete batch with existing %s',
                    implode(', ', $existingRelations)
                )
            );
        }
    }
}
