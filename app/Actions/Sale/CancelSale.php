<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Stock\AddStock;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelSale
{
    public function __construct(
        private AddStock $addStock,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, ?string $reason = null): Sale
    {
        return DB::transaction(function () use ($sale, $reason): Sale {
            if (! $sale->status->canTransitionTo(SaleStatusEnum::Cancelled)) {
                throw new StateTransitionException($sale->status->value, SaleStatusEnum::Cancelled->value);
            }

            if ($sale->payments()->active()->exists()) {
                throw new InvalidOperationException(
                    'cancel',
                    'Sale',
                    'Cannot cancel a sale with active payments. Void payments first.'
                );
            }

            if ($sale->status === SaleStatusEnum::Completed) {
                $sale->load('items.batch');

                $sale->items->each(function ($item) use ($sale, $reason): void {
                    if ($item->batch instanceof Batch) {
                        $this->addStock->handle(
                            batch: $item->batch,
                            quantity: $item->quantity,
                            reference: $sale,
                            note: $reason ?? "Sale cancelled: $sale->reference_no",
                        );
                    }
                });
            }

            $sale->forceFill(['status' => SaleStatusEnum::Cancelled])->save();

            return $sale->refresh();
        });
    }
}
