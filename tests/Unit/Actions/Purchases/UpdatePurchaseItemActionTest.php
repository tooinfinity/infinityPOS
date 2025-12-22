<?php

declare(strict_types=1);

use App\Actions\Purchases\UpdatePurchaseItem;
use App\Data\Purchases\UpdatePurchaseItemData;
use App\Models\PurchaseItem;
use App\Models\User;

it('may update a purchase item', function (): void {
    $user = User::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create([
        'quantity' => 10,
        'cost' => 1000,
        'batch_number' => 'OLD-BATCH',
    ]);

    $action = resolve(UpdatePurchaseItem::class);

    $data = UpdatePurchaseItemData::from([
        'quantity' => 20,
        'cost' => 1500,
        'discount' => null,
        'tax_amount' => null,
        'total' => null,
        'batch_number' => 'NEW-BATCH',
        'expiry_date' => null,
    ]);

    $updatedItem = $action->handle($purchaseItem, $data);

    expect($updatedItem->quantity)->toBe(20)
        ->and($updatedItem->cost)->toBe(1500)
        ->and($updatedItem->batch_number)->toBe('NEW-BATCH');
});
