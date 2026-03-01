<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\RemovePurchaseReturnItem;
use App\Exceptions\StateTransitionException;
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

it('throws exception when removing item from non-pending return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();
    $item = PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create();

    $action = resolve(RemovePurchaseReturnItem::class);

    $action->handle($item);
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "pending"');
