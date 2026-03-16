<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Exceptions\InvalidOperationException;
use App\Models\Product;
use Throwable;

final readonly class DeleteProduct
{
    /**
     * @throws Throwable
     */
    public function handle(Product $product): bool
    {
        return $product->getConnection()->transaction(function () use ($product): bool {
            $this->ensureNoRelatedRecords($product);

            return (bool) $product->delete();
        });
    }

    /**
     * @throws InvalidOperationException
     */
    private function ensureNoRelatedRecords(Product $product): void
    {
        $existing = array_filter([
            'batches' => $product->batches()->exists(),
            'stockMovements' => $product->stockMovements()->exists(),
            'purchaseItems' => $product->purchaseItems()->exists(),
            'saleItems' => $product->saleItems()->exists(),
            'stockTransferItems' => $product->stockTransferItems()->exists(),
            'saleReturnItems' => $product->saleReturnItems()->exists(),
            'purchaseReturnItems' => $product->purchaseReturnItems()->exists(),
        ]);

        if ($existing !== []) {
            throw new InvalidOperationException(
                'delete',
                'Product',
                sprintf('Cannot delete product with existing %s', implode(', ', array_keys($existing)))
            );
        }
    }
}
