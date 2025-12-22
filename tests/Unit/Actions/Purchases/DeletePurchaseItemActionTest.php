<?php

declare(strict_types=1);

use App\Actions\Purchases\DeletePurchaseItem;
use App\Models\PurchaseItem;

it('may delete a purchase item', function (): void {
    $purchaseItem = PurchaseItem::factory()->create();
    $action = resolve(DeletePurchaseItem::class);

    $result = $action->handle($purchaseItem);

    expect($result)->toBeTrue();
    expect(PurchaseItem::query()->find($purchaseItem->id))->toBeNull();
});
