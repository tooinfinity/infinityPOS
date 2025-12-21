<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CompleteSale
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, int $userId): Sale
    {
        if ($sale->status->isCompleted()) {
            return $sale;
        }

        throw_if($sale->status->isCancelled(), InvalidArgumentException::class, 'Cannot complete a cancelled sale.');

        return DB::transaction(function () use ($sale, $userId): Sale {
            $sale->update([
                'status' => SaleStatusEnum::COMPLETED,
                'updated_by' => $userId,
            ]);

            foreach ($sale->items as $item) {
                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'store_id' => $sale->store_id,
                    'quantity' => -$item->quantity,
                    'source_type' => Sale::class,
                    'source_id' => $sale->id,
                    'batch_number' => $item->batch_number,
                    'notes' => 'Sale completed: '.$sale->reference,
                    'created_by' => $userId,
                ]);
            }

            return $sale;
        });
    }
}
