<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Purchases\CalculatePurchaseTotals;
use App\Actions\Purchases\CreatePurchaseItem;
use App\Actions\Purchases\DeletePurchaseItem;
use App\Actions\Purchases\UpdatePurchaseItem;
use App\Data\Purchases\CreatePurchaseItemData;
use App\Data\Purchases\UpdatePurchaseItemData;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\RedirectResponse;

final readonly class PurchaseItemController
{
    public function __construct(
        private CalculatePurchaseTotals $calculateTotals,
    ) {}

    public function store(CreatePurchaseItemData $data, Purchase $purchase, CreatePurchaseItem $action): RedirectResponse
    {
        $action->handle($purchase, $data);

        $this->calculateTotals->handle($purchase);

        return back();
    }

    public function update(UpdatePurchaseItemData $data, Purchase $purchase, PurchaseItem $item, UpdatePurchaseItem $action): RedirectResponse
    {
        $action->handle($item, $data);

        $this->calculateTotals->handle($purchase);

        return back();
    }

    public function destroy(Purchase $purchase, PurchaseItem $item, DeletePurchaseItem $action): RedirectResponse
    {
        $action->handle($item);

        $this->calculateTotals->handle($purchase);

        return back();
    }
}
