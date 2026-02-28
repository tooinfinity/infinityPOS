<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;
use Throwable;

final readonly class ValidateReturnAgainstOriginal
{
    /**
     * @throws RuntimeException|Throwable
     */
    public function handle(
        SaleReturnItem|PurchaseReturnItem $item,
        int $productId,
        ?int $batchId,
        int $quantity,
    ): void {
        /** @var SaleReturn|PurchaseReturn $returnModel */
        $returnModel = $item instanceof SaleReturnItem ? $item->saleReturn : $item->purchaseReturn;
        /** @var Sale|Purchase|null $originalOrder */
        $originalOrder = $returnModel instanceof SaleReturn ? $returnModel->sale : $returnModel->purchase;

        if ($originalOrder === null) {
            throw new RuntimeException('Return must be associated with an original order.');
        }

        $originalItem = $this->findOriginalItem($originalOrder, $productId, $batchId);

        throw_if($originalItem === null, RuntimeException::class, 'Product is not part of the original order or batch does not match.');

        $alreadyReturned = $this->getAlreadyReturnedQuantity($returnModel, $originalOrder, $productId, $batchId, $item instanceof SaleReturnItem ? SaleReturnItem::class : PurchaseReturnItem::class);

        $maxReturnable = $originalItem->quantity - $alreadyReturned;

        throw_if(
            $quantity > $maxReturnable,
            RuntimeException::class,
            "Cannot return more than originally purchased. Original: {$originalItem->quantity}, Already returned: {$alreadyReturned}, Remaining: {$maxReturnable}"
        );
    }

    /**
     * @throws RuntimeException
     * @throws Throwable
     */
    public function validateNewReturn(
        SaleReturn $saleReturn,
        int $productId,
        ?int $batchId,
        int $quantity,
    ): void {
        /** @var Sale|null $sale */
        $sale = $saleReturn->sale;

        if ($sale === null) {
            throw new RuntimeException('Sale return must be associated with a sale.');
        }

        $originalItem = $this->findOriginalItem($sale, $productId, $batchId);

        throw_if($originalItem === null, RuntimeException::class, 'Product is not part of the original sale or batch does not match.');

        $alreadyReturned = SaleReturnItem::query()
            ->whereHas('saleReturn', fn (Builder $q) => $q->where('sale_id', $sale->id))
            ->where('product_id', $productId)
            ->where('batch_id', $batchId)
            ->sum('quantity');

        $maxReturnable = $originalItem->quantity - $alreadyReturned;

        throw_if(
            $quantity > $maxReturnable,
            RuntimeException::class,
            "Cannot return more than originally purchased. Original: {$originalItem->quantity}, Already returned: {$alreadyReturned}, Remaining: {$maxReturnable}"
        );
    }

    /**
     * @throws RuntimeException
     * @throws Throwable
     */
    public function validateNewReturnForPurchase(
        PurchaseReturn $purchaseReturn,
        int $productId,
        ?int $batchId,
        int $quantity,
    ): void {
        /** @var Purchase|null $purchase */
        $purchase = $purchaseReturn->purchase;

        if ($purchase === null) {
            throw new RuntimeException('Purchase return must be associated with a purchase.');
        }

        $originalItem = $this->findOriginalItem($purchase, $productId, $batchId);

        throw_if($originalItem === null, RuntimeException::class, 'Product is not part of the original purchase or batch does not match.');

        $alreadyReturned = PurchaseReturnItem::query()
            ->whereHas('purchaseReturn', fn (Builder $q) => $q->where('purchase_id', $purchase->id))
            ->where('product_id', $productId)
            ->where('batch_id', $batchId)
            ->sum('quantity');

        $maxReturnable = $originalItem->quantity - $alreadyReturned;

        throw_if(
            $quantity > $maxReturnable,
            RuntimeException::class,
            "Cannot return more than originally purchased. Original: {$originalItem->quantity}, Already returned: {$alreadyReturned}, Remaining: {$maxReturnable}"
        );
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
        $query = SaleReturnItem::query();

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
