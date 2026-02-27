<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;

final readonly class RecalculateParentTotal
{
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $model): void
    {
        $model->refresh();

        $totalAmount = match ($model::class) {
            Sale::class => $model->items->sum('subtotal'),
            SaleReturn::class, PurchaseReturn::class => $model->items()->lockForUpdate()->sum('subtotal'),
            Purchase::class => PurchaseItem::query()
                ->where('purchase_id', $model->id)
                ->lockForUpdate()
                ->sum('subtotal'),
        };

        $model->forceFill(['total_amount' => $totalAmount])->save();
    }
}
