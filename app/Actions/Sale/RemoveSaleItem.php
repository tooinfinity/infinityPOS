<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Actions\Shared\RecalculateParentTotal;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RemoveSaleItem
{
    public function __construct(
        private RecalculateParentTotal $recalculateTotal,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(SaleItem $item): Sale
    {
        return DB::transaction(function () use ($item): Sale {
            $sale = $item->sale;

            $item->delete();

            $this->recalculateTotal->handle($sale);

            return $sale->refresh();
        });
    }
}
