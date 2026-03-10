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
     * @return Collection<int, int> keyed by product_id
     */
    public function handle(Sale $sale): Collection
    {
        $sale->loadMissing('items');

        /** @var Collection<int, int> $alreadyReturned */
        $alreadyReturned = $sale->returns()
            ->with('items')
            ->get()
            ->flatMap(
                /** @return Collection<int, SaleReturnItem> */
                fn (SaleReturn $return): Collection => $return->items
            )
            ->groupBy('product_id')
            ->map(
                /** @param Collection<int, SaleReturnItem> $items */
                function (Collection $items): int {
                    /** @var int|float $sum */
                    $sum = $items->sum('quantity');

                    return (int) $sum;
                }
            );

        /** @var Collection<int, int> $saleItems */
        $saleItems = $sale->items
            ->groupBy('product_id')
            ->map(
                /**
                 * @param  Collection<int, SaleItem>  $items
                 */
                function (Collection $items, int $productId) use ($alreadyReturned): int {
                    /** @var int|float $sold */
                    $sold = $items->sum('quantity');

                    $soldQty = (int) $sold;

                    $returnedQty = $alreadyReturned->get($productId, 0);

                    return max(0, $soldQty - $returnedQty);
                }
            );

        return $saleItems;
    }

    /**
     * Validates that all requested return quantities are within returnable limits.
     *
     * @param  Collection<int, int>  $returnableMap  keyed by product_id
     * @param  DataCollection<int, SaleReturnItemData>  $items
     *
     * @throws InvalidOperationException
     */
    public function validate(Collection $returnableMap, DataCollection $items): void
    {
        foreach ($items as $item) {
            /** @var SaleReturnItemData $item */
            $maxReturnable = $returnableMap->get($item->product_id, 0);

            if ($item->quantity > $maxReturnable) {
                throw new InvalidOperationException(
                    'return',
                    'SaleItem',
                    sprintf(
                        'Product #%d: requested return quantity %d exceeds returnable quantity %d.',
                        $item->product_id,
                        $item->quantity,
                        $maxReturnable,
                    )
                );
            }
        }
    }
}
