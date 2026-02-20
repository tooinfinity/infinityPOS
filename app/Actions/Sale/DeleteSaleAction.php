<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class DeleteSaleAction
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            $this->validateSaleCanBeDeleted($sale);

            $sale->items()->delete();
            $sale->delete();
        });
    }

    private function validateSaleCanBeDeleted(Sale $sale): void
    {
        if ($sale->status !== SaleStatusEnum::Pending) {
            throw new RuntimeException(
                "Can only delete pending sales. Current status: {$sale->status->value}"
            );
        }
    }
}
