<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Exceptions\InvalidOperationException;
use App\Exceptions\ItemNotFoundException;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

final readonly class ValidateReturnAgainstOriginal
{
    /**
     * @throws ItemNotFoundException|Throwable
     */
    public function handle(
        SaleReturnItem|PurchaseReturnItem $item,
        int $productId,
        ?int $batchId,
        int $quantity,
    ): void {
        /** @var SaleReturn|PurchaseReturn $returnModel */
        $returnModel = $item instanceof SaleReturnItem ? $item->saleReturn : $item->purchaseReturn;
        /** @var Sale|Purchase $originalOrder */
        $originalOrder = $returnModel instanceof SaleReturn ? $returnModel->sale : $returnModel->purchase;

        $originalItem = $this->findOriginalItem($originalOrder, $productId, $batchId);

        throw_if($originalItem === null, ItemNotFoundException::class, 'Product', 'original order', 'Product is not part of the original order or batch does not match.');

        $alreadyReturned = $this->getAlreadyReturnedQuantity($returnModel, $originalOrder, $productId, $batchId, $item instanceof SaleReturnItem ? SaleReturnItem::class : PurchaseReturnItem::class);

        $maxReturnable = $originalItem->quantity - $alreadyReturned;

        if ($quantity > $maxReturnable) {
            throw new InvalidOperationException(
                'return',
                'item',
                "Cannot return more than originally purchased. Original: $originalItem->quantity, Already returned: $alreadyReturned, Remaining: $maxReturnable"
            );
        }
    }

    /**
     * @throws ItemNotFoundException
     * @throws Throwable
     */
    public function validateNewReturn(
        SaleReturn $saleReturn,
        int $productId,
        ?int $batchId,
        int $quantity,
    ): void {
        $sale = $saleReturn->sale;

        $originalItem = $this->findOriginalItem($sale, $productId, $batchId);

        throw_if($originalItem === null, ItemNotFoundException::class, 'Product', 'original sale', 'Product is not part of the original sale or batch does not match.');

        $alreadyReturned = SaleReturnItem::query()
            ->whereHas('saleReturn', fn (Builder $q) => $q->where('sale_id', $sale->id))
            ->where('product_id', $productId)
            ->where('batch_id', $batchId)
            ->sum('quantity');

        $maxReturnable = $originalItem->quantity - $alreadyReturned;

        if ($quantity > $maxReturnable) {
            throw new InvalidOperationException(
                'return',
                'item',
                "Cannot return more than originally purchased. Original: $originalItem->quantity, Already returned: $alreadyReturned, Remaining: $maxReturnable"
            );
        }
    }

    /**
     * @throws ItemNotFoundException
     * @throws Throwable
     */
    public function validateNewReturnForPurchase(
        PurchaseReturn $purchaseReturn,
        int $productId,
        ?int $batchId,
        int $quantity,
    ): void {
        $purchase = $purchaseReturn->purchase;

        $originalItem = $this->findOriginalItem($purchase, $productId, $batchId);

        throw_if($originalItem === null, ItemNotFoundException::class, 'Product', 'original purchase', 'Product is not part of the original purchase or batch does not match.');

        $alreadyReturned = PurchaseReturnItem::query()
            ->whereHas('purchaseReturn', fn (Builder $q) => $q->where('purchase_id', $purchase->id))
            ->where('product_id', $productId)
            ->where('batch_id', $batchId)
            ->sum('quantity');

        $maxReturnable = $originalItem->quantity - $alreadyReturned;

        if ($quantity > $maxReturnable) {
            throw new InvalidOperationException(
                'return',
                'item',
                "Cannot return more than originally purchased. Original: $originalItem->quantity, Already returned: $alreadyReturned, Remaining: $maxReturnable"
            );
        }
    }

    private function findOriginalItem(Sale|Purchase $order, int $productId, ?int $batchId): SaleItem|PurchaseItem|null
    {
        $items = $order instanceof Sale ? $order->items : $order->items;

        return $items
            ->where('product_id', $productId)
            ->where('batch_id', $batchId)
            ->first();
    }

    private function getAlreadyReturnedQuantity(
        SaleReturn|PurchaseReturn $returnModel,
        Sale|Purchase $originalOrder,
        int $productId,
        ?int $batchId,
        string $returnItemClass,
    ): int {

        if ($returnItemClass === SaleReturnItem::class) {
            $query = SaleReturnItem::query()
                ->whereHas('saleReturn', fn (Builder $q) => $q->where('sale_id', $originalOrder->id))
                ->where('product_id', $productId)
                ->where('batch_id', $batchId);

        } else {
            $query = PurchaseReturnItem::query()
                ->whereHas('purchaseReturn', fn (Builder $q) => $q->where('purchase_id', $originalOrder->id))
                ->where('product_id', $productId)
                ->where('batch_id', $batchId);

        }
        if (isset($returnModel->id)) {
            $query->where('id', '!=', $returnModel->id);
        }

        /** @var int $sum */
        $sum = $query->sum('quantity');

        return $sum;
    }
}
