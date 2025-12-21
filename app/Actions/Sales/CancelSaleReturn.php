<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Enums\SaleReturnStatusEnum;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelSaleReturn
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, int $userId): SaleReturn
    {
        if ($saleReturn->status->isCancelled()) {
            return $saleReturn;
        }

        return DB::transaction(function () use ($saleReturn, $userId): SaleReturn {
            if ($saleReturn->status->isCompleted()) {
                foreach ($saleReturn->items as $item) {
                    StockMovement::query()->create([
                        'product_id' => $item->product_id,
                        'store_id' => $saleReturn->store_id,
                        'quantity' => -$item->quantity,
                        'source_type' => SaleReturn::class,
                        'source_id' => $saleReturn->id,
                        'batch_number' => null,
                        'notes' => 'Sale return cancelled: '.$saleReturn->reference,
                        'created_by' => $userId,
                    ]);
                }
            }

            $saleReturn->update([
                'status' => SaleReturnStatusEnum::CANCELLED,
                'updated_by' => $userId,
            ]);

            return $saleReturn;
        });
    }
}
