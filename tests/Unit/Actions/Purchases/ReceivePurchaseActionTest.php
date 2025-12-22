<?php

declare(strict_types=1);

use App\Actions\Purchases\ReceivePurchase;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\User;

it('may receive a pending purchase', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 20,
    ]);

    $action = resolve(ReceivePurchase::class);

    $receivedPurchase = $action->handle($purchase, $user->id);

    expect($receivedPurchase->status)->toBe(PurchaseStatusEnum::RECEIVED)
        ->and($receivedPurchase->updated_by)->toBe($user->id);

    // Check stock movements created
    $stockMovement = StockMovement::query()
        ->where('source_type', Purchase::class)
        ->where('source_id', $purchase->id)
        ->first();

    expect($stockMovement)->not->toBeNull()
        ->and($stockMovement->quantity)->toBe(20)
        ->and($stockMovement->product_id)->toBe($product->id);
});

it('cannot receive a cancelled purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(ReceivePurchase::class);

    $action->handle($purchase, $user->id);
})->throws(InvalidArgumentException::class, 'Cannot receive a cancelled purchase.');

it('returns already received purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::RECEIVED,
        'created_by' => $user->id,
    ]);

    $action = resolve(ReceivePurchase::class);
    $receivedPurchase = $action->handle($purchase, $user->id);

    expect($receivedPurchase->status)->toBe(PurchaseStatusEnum::RECEIVED);
});
