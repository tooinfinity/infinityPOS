<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Data\SaleReturn\SaleReturnItemData;
use App\Exceptions\InvalidOperationException;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

final readonly class ResolveReturnableQuantity
{
    /**
     * @return Collection<string, int> keyed by "product_id:batch_id" (batch_id is "null" for non-tracked)
     */
    public function handle(Sale $sale): Collection
    {
        $sale->loadMissing('items');

        /** @var Collection<string, int> $alreadyReturned keyed by "product_id:batch_id" */
        $alreadyReturned = $sale->returns()
            ->with('items')
            ->get()
            ->flatMap(
                /** @return Collection<int, SaleReturnItem> */
                fn (SaleReturn $return): Collection => $return->items
            )
            ->groupBy(fn (SaleReturnItem $item): string => $this->buildKey($item->product_id, $item->batch_id))
            ->map(
                /** @param Collection<int, SaleReturnItem> $items */
                function (Collection $items): int {
                    /** @var int|float $sum */
                    $sum = $items->sum('quantity');

                    return (int) $sum;
                }
            );

        /** @var Collection<string, int> keyed by "product_id:batch_id" */
        $saleItems = $sale->items
            ->groupBy(fn (SaleItem $item): string => $this->buildKey($item->product_id, $item->batch_id))
            ->map(
                /**
                 * @param  Collection<int, SaleItem>  $items
                 */
                function (Collection $items, string $key) use ($alreadyReturned): int {
                    /** @var int|float $sold */
                    $sold = $items->sum('quantity');

                    $soldQty = (int) $sold;

                    $returnedQty = $alreadyReturned->get($key, 0);

                    return max(0, $soldQty - $returnedQty);
                }
            );

        return $saleItems;
    }

    /**
     * Validates that all requested return quantities are within returnable limits.
     *
     * @param  Collection<string, int>  $returnableMap  keyed by "product_id:batch_id"
     * @param  DataCollection<int, SaleReturnItemData>  $items
     *
     * @throws InvalidOperationException
     */
    public function validate(Collection $returnableMap, DataCollection $items): void
    {
        foreach ($items as $item) {
            /** @var SaleReturnItemData $item */
            $key = $this->buildKey($item->product_id, $item->batch_id);
            $maxReturnable = $returnableMap->get($key, 0);

            if ($item->quantity > $maxReturnable) {
                throw new InvalidOperationException(
                    'return',
                    'SaleItem',
                    sprintf(
                        'Product #%d (batch #%s): requested return quantity %d exceeds returnable quantity %d.',
                        $item->product_id,
                        $item->batch_id ?? 'null',
                        $item->quantity,
                        $maxReturnable,
                    )
                );
            }
        }
    }

    /**
     * Builds a composite key from product_id and batch_id.
     */
    private function buildKey(int $productId, ?int $batchId): string
    {
        return $productId.':'.($batchId ?? 'null');
    }
}
