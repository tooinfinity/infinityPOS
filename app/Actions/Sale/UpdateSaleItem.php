<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Shared\RecalculateParentTotal;
use App\Actions\Stock\ValidateStockForPendingSale;
use App\Data\Sale\UpdateSaleItemData;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateSaleItem
{
    public function __construct(
        private RecalculateParentTotal $recalculateTotal,
        private ValidateStockForPendingSale $validateStockForPendingSale,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleItem $item, UpdateSaleItemData $data): SaleItem
    {
        return DB::transaction(function () use ($item, $data): SaleItem {
            $batchIdValue = $data->batch_id;
            $quantityValue = $data->quantity;

            $isBatchIdProvided = $batchIdValue !== null && ! $batchIdValue instanceof Optional;
            $isQuantityProvided = $quantityValue !== null && ! $quantityValue instanceof Optional;

            $batchIdChanged = $isBatchIdProvided && $batchIdValue !== $item->batch_id;
            $quantityIncreased = $isQuantityProvided && $quantityValue > $item->quantity;

            if ($batchIdChanged) {
                $quantity = $isQuantityProvided ? $quantityValue : $item->quantity;
                /** @var int $batchIdValue */
                /** @var int $quantity */
                $this->validateStockForPendingSale->handle($item->sale, $batchIdValue, $quantity, $item->id);
            } elseif ($quantityIncreased && $item->batch_id !== null) {
                /** @var int $quantityValue */
                $this->validateStockForPendingSale->handle($item->sale, $item->batch_id, $quantityValue, $item->id);
            }

            $quantity = is_int($data->quantity) ? $data->quantity : $item->quantity;
            $unitPrice = is_numeric($data->unit_price) ? $data->unit_price : $item->unit_price;
            $unitCost = is_numeric($data->unit_cost) ? $data->unit_cost : $item->unit_cost;
            $batchId = is_int($data->batch_id) ? $data->batch_id : $item->batch_id;

            $item->forceFill([
                'batch_id' => $batchId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_cost' => $unitCost,
                'subtotal' => $quantity * $unitPrice,
            ])->save();

            $this->recalculateTotal->handle($item->sale);

            return $item->refresh();
        });
    }
}
