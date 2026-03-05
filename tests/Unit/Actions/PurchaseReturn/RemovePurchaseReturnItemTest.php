<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\RemovePurchaseReturnItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

it('removes item from pending purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    $item = PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create();

    $action = resolve(RemovePurchaseReturnItem::class);

    $result = $action->handle($item);

    expect($result)->toBeTrue()
        ->and($item->exists)->toBeFalse();
});
