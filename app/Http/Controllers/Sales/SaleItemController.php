<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sales;

use App\Actions\Sale\AddSaleItem;
use App\Actions\Sale\RemoveSaleItem;
use App\Actions\Sale\UpdateSaleItem;
use App\Data\Sale\SaleItemData;
use App\Data\Sale\UpdateSaleItemData;
use App\Http\Requests\Sale\AddSaleItemRequest;
use App\Http\Requests\Sale\RemoveSaleItemRequest;
use App\Http\Requests\Sale\UpdateSaleItemRequest;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Throwable;

final readonly class SaleItemController
{
    public function __construct(
        private AddSaleItem $addSaleItem,
        private UpdateSaleItem $updateSaleItem,
        private RemoveSaleItem $removeSaleItem,
    ) {}

    /**
     * @throws Throwable
     */
    public function store(AddSaleItemRequest $request, Sale $sale): RedirectResponse
    {
        $data = SaleItemData::from($request->validated());

        $this->addSaleItem->handle($sale, $data);

        return back();
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateSaleItemRequest $request, Sale $sale, SaleItem $item): RedirectResponse
    {
        $data = UpdateSaleItemData::from($request->validated());

        $this->updateSaleItem->handle($item, $data);

        return back();
    }

    /**
     * @throws Throwable
     */
    public function destroy(RemoveSaleItemRequest $request, Sale $sale, SaleItem $item): RedirectResponse
    {
        $this->removeSaleItem->handle($item);

        return back();
    }
}
