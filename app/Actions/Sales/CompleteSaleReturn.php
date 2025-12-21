<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Enums\SaleReturnStatusEnum;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CompleteSaleReturn
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, int $userId): SaleReturn
    {
        if ($saleReturn->status->isCompleted()) {
            return $saleReturn;
        }

        throw_if($saleReturn->status->isCancelled(), InvalidArgumentException::class, 'Cannot complete a cancelled sale return.');

        return DB::transaction(function () use ($saleReturn, $userId): SaleReturn {
            $saleReturn->update([
                'status' => SaleReturnStatusEnum::COMPLETED,
                'updated_by' => $userId,
            ]);

            foreach ($saleReturn->items as $item) {
                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'store_id' => $saleReturn->store_id,
                    'quantity' => $item->quantity,
                    'source_type' => SaleReturn::class,
                    'source_id' => $saleReturn->id,
                    'batch_number' => null,
                    'notes' => 'Sale return completed: '.$saleReturn->reference,
                    'created_by' => $userId,
                ]);
            }

            return $saleReturn;
        });
    }
}
