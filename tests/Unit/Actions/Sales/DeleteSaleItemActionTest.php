<?php

declare(strict_types=1);

use App\Actions\Sales\DeleteSaleItem;
use App\Models\SaleItem;

it('may delete a sale item', function (): void {
    $saleItem = SaleItem::factory()->create();
    $action = resolve(DeleteSaleItem::class);

    $result = $action->handle($saleItem);

    expect($result)->toBeTrue()
        ->and(SaleItem::query()->find($saleItem->id))->toBeNull();
});
