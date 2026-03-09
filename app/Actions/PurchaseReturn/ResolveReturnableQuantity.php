<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Exceptions\InvalidOperationException;
use App\Models\Purchase;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

final readonly class ResolveReturnableQuantity
{
    /**
     * @return Collection<int, int> keyed by product_id
     */
    public function handle(Purchase $purchase): Collection
    {
        $purchase->loadMissing('items');

        $alreadyReturned = $purchase->returns()
            ->with('items')
            ->get()
            ->flatMap(fn ($return) => $return->items)
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        /** @var Collection<int, int> $purchaseItems */
        $purchaseItems = $purchase->items
            ->groupBy('product_id')
            ->map(function ($items, int $productId) use ($alreadyReturned): int {
                /** @var int $receivedQty */
                $receivedQty = $items->sum('received_quantity');
                /** @var int $returnedQty */
                $returnedQty = $alreadyReturned->get($productId, 0);

                return max(0, $receivedQty - $returnedQty);
            });

        return $purchaseItems;
    }

    /**
     * @param  Collection<int, int>  $returnableMap  keyed by product_id
     * @param  DataCollection<int, PurchaseReturnItemData>  $items
     *
     * @throws InvalidOperationException
     */
    public function validate(Collection $returnableMap, DataCollection $items): void
    {
        foreach ($items as $item) {
            $maxReturnable = $returnableMap->get($item->product_id, 0);

            if ($item->quantity > $maxReturnable) {
                throw new InvalidOperationException(
                    'return',
                    'PurchaseItem',
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
