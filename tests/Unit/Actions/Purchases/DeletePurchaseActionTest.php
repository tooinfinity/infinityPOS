<?php

declare(strict_types=1);

use App\Actions\Purchases\DeletePurchase;
use App\Enums\PurchaseStatusEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;

it('may delete a pending purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);
    PurchaseItem::factory()->create(['purchase_id' => $purchase->id]);
    Payment::factory()->create([
        'related_type' => Purchase::class,
        'related_id' => $purchase->id,
        'created_by' => $user->id,
    ]);

    $action = resolve(DeletePurchase::class);

    $result = $action->handle($purchase);

    expect($result)->toBeTrue();
    expect(Purchase::query()->find($purchase->id))->toBeNull();
    expect(PurchaseItem::query()->where('purchase_id', $purchase->id)->count())->toBe(0);
    expect(Payment::query()->where('related_id', $purchase->id)->where('related_type', Purchase::class)->count())->toBe(0);
});

it('cannot delete a received purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::RECEIVED,
        'created_by' => $user->id,
    ]);

    $action = resolve(DeletePurchase::class);

    $action->handle($purchase);
})->throws(InvalidArgumentException::class, 'Cannot delete a received purchase. Please cancel it first.');
