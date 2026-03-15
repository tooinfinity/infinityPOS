<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Stock\DeductStock;
use App\Enums\SaleStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteSale
{
    public function __construct(
        private DeductStock $deductStock,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale): Sale {
            if (! $sale->status->canTransitionTo(SaleStatusEnum::Completed)) {
                throw new StateTransitionException($sale->status->value, SaleStatusEnum::Completed->value);
            }

            $sale->load('items.batch');

            $sale->items->each(function (SaleItem $item) use ($sale): void {
                if ($item->batch instanceof Batch) {
                    $this->deductStock->handle(
                        batch: $item->batch,
                        quantity: $item->quantity,
                        reference: $sale,
                        note: "Sale completed: $sale->reference_no",
                    );
                }
            });

            $sale->forceFill(['status' => SaleStatusEnum::Completed])->save();

            return $sale->refresh()->load('items');
        });
    }
}
