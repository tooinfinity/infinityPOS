<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class RemoveSaleItem
{
    /**
     * @throws Throwable
     */
    public function handle(SaleItem $item): Sale
    {
        return DB::transaction(function () use ($item): Sale {
            $sale = $item->sale;

            $this->validateSaleIsPending($sale);

            $item->delete();

            $this->recalculateSaleTotals($sale);

            return $sale->refresh();
        });
    }

    private function validateSaleIsPending(Sale $sale): void
    {
        if ($sale->status !== SaleStatusEnum::Pending) {
            throw new RuntimeException(
                "Can only remove items from pending sales. Current status: {$sale->status->value}"
            );
        }
    }

    private function recalculateSaleTotals(Sale $sale): void
    {
        $sale->load('items');
        $totalAmount = $sale->items->sum('subtotal');
        $sale->forceFill(['total_amount' => $totalAmount])->save();
    }
}
