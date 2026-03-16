<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use Throwable;

final readonly class DeleteBatch
{
    /**
     * @throws Throwable
     */
    public function handle(Batch $batch): bool
    {
        return $batch->getConnection()->transaction(function () use ($batch): bool {
            $this->ensureNoRelatedRecords($batch);

            return (bool) $batch->delete();
        });
    }

    /**
     * @throws InvalidOperationException
     */
    private function ensureNoRelatedRecords(Batch $batch): void
    {
        $existing = array_filter([
            'stockMovements' => $batch->stockMovements()->exists(),
            'purchaseItems' => $batch->purchaseItems()->exists(),
            'saleItems' => $batch->saleItems()->exists(),
            'stockTransferItems' => $batch->stockTransferItems()->exists(),
            'saleReturnItems' => $batch->saleReturnItems()->exists(),
            'purchaseReturnItems' => $batch->purchaseReturnItems()->exists(),
        ]);

        if ($existing !== []) {
            throw new InvalidOperationException(
                'delete',
                'Batch',
                sprintf('Cannot delete batch with existing %s', implode(', ', array_keys($existing)))
            );
        }
    }
}
