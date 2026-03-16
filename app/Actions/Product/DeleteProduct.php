<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Exceptions\InvalidOperationException;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteProduct
{
    /**
     * @throws Throwable
     */
    public function handle(Product $product): bool
    {
        return DB::transaction(function () use ($product): bool {
            $this->ensureNoRelatedRecords($product);

            return (bool) $product->delete();
        });
    }

    /**
     * @throws InvalidOperationException
     *
     * FIX: replaced 7 individual EXISTS queries with a single SQL query.
     */
    private function ensureNoRelatedRecords(Product $product): void
    {
        /** @var array<int, int> $result */
        $result = DB::selectOne(
            <<<'SQL'
            SELECT
                (SELECT COUNT(*) FROM batches                WHERE product_id = ?) AS batches,
                (SELECT COUNT(*) FROM stock_movements        WHERE product_id = ?) AS stockMovements,
                (SELECT COUNT(*) FROM purchase_items         WHERE product_id = ?) AS purchaseItems,
                (SELECT COUNT(*) FROM sale_items             WHERE product_id = ?) AS saleItems,
                (SELECT COUNT(*) FROM stock_transfer_items   WHERE product_id = ?) AS stockTransferItems,
                (SELECT COUNT(*) FROM sale_return_items      WHERE product_id = ?) AS saleReturnItems,
                (SELECT COUNT(*) FROM purchase_return_items  WHERE product_id = ?) AS purchaseReturnItems
            SQL,
            array_fill(0, 7, $product->id),
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
                'Product',
                sprintf('Cannot delete product with existing %s', implode(', ', $existing))
            );
        }
    }
}
