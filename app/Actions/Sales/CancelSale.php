<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelSale
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, int $userId): Sale
    {
        if ($sale->status->isCancelled()) {
            return $sale;
        }

        return DB::transaction(static function () use ($sale, $userId): Sale {
            if ($sale->status->isCompleted()) {
                foreach ($sale->items as $item) {
                    StockMovement::query()->create([
                        'product_id' => $item->product_id,
                        'store_id' => $sale->store_id,
                        'quantity' => $item->quantity,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                        'batch_number' => $item->batch_number,
                        'notes' => 'Sale cancelled: '.$sale->reference,
                        'created_by' => $userId,
                    ]);
                }
            }

            $sale->update([
                'status' => SaleStatusEnum::CANCELLED,
                'updated_by' => $userId,
            ]);

            return $sale;
        });
    }
}
