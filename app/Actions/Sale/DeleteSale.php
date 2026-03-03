<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteSale
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            $this->validateSaleCanBeDeleted($sale);

            $sale->items()->delete();
            $sale->payments()->delete();
            $sale->delete();
        });
    }

    /**
     * @throws InvalidOperationException
     */
    private function validateSaleCanBeDeleted(Sale $sale): void
    {
        if ($sale->status !== SaleStatusEnum::Pending) {
            throw new InvalidOperationException(
                'delete',
                'Sale',
                "Current status: {$sale->status->value}"
            );
        }
    }
}
