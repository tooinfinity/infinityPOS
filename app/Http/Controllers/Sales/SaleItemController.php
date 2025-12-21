<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\CalculateSaleTotals;
use App\Actions\Sales\CreateSaleItem;
use App\Actions\Sales\DeleteSaleItem;
use App\Actions\Sales\UpdateSaleItem;
use App\Data\Sales\CreateSaleItemData;
use App\Data\Sales\UpdateSaleItemData;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;

final readonly class SaleItemController
{
    public function __construct(
        private CalculateSaleTotals $calculateTotals,
    ) {}

    public function store(CreateSaleItemData $data, Sale $sale, CreateSaleItem $action): RedirectResponse
    {
        $action->handle($sale, $data);

        $this->calculateTotals->handle($sale);

        return back();
    }

    public function update(UpdateSaleItemData $data, Sale $sale, SaleItem $item, UpdateSaleItem $action): RedirectResponse
    {
        $action->handle($item, $data);

        $this->calculateTotals->handle($sale);

        return back();
    }

    public function destroy(Sale $sale, SaleItem $item, DeleteSaleItem $action): RedirectResponse
    {
        $action->handle($item);

        $this->calculateTotals->handle($sale);

        return back();
    }
}
