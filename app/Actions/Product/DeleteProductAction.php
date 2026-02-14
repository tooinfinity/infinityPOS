<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final readonly class DeleteProductAction
{
    /**
     * @throws Throwable
     */
    public function handle(Product $product): bool
    {
        return DB::transaction(function () use ($product): bool {
            $this->ensureNoRelatedRecords($product);

            if ($product->image !== null) {
                Storage::disk('public')->delete($product->image);
            }

            return (bool) $product->delete();
        });
    }

    private function ensureNoRelatedRecords(Product $product): void
    {
        $relations = [
            'batches' => $product->batches()->exists(),
            'stockMovements' => $product->stockMovements()->exists(),
            'purchaseItems' => $product->purchaseItems()->exists(),
            'saleItems' => $product->saleItems()->exists(),
            'stockTransferItems' => $product->stockTransferItems()->exists(),
            'saleReturnItems' => $product->saleReturnItems()->exists(),
            'purchaseReturnItems' => $product->purchaseReturnItems()->exists(),
        ];

        $existingRelations = array_keys(array_filter($relations));

        if ($existingRelations !== []) {
            throw new RuntimeException(
                sprintf(
                    'Cannot delete product with existing %s',
                    implode(', ', $existingRelations)
                )
            );
        }
    }
}
