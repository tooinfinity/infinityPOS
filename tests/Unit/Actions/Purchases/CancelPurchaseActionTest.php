<?php

declare(strict_types=1);

use App\Actions\Purchases\CancelPurchase;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\User;

it('may cancel a pending purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelPurchase::class);

    $cancelledPurchase = $action->handle($purchase, $user->id);

    expect($cancelledPurchase->status)->toBe(PurchaseStatusEnum::CANCELLED)
        ->and($cancelledPurchase->updated_by)->toBe($user->id);
});

it('may cancel a received purchase and reverse stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::RECEIVED,
        'created_by' => $user->id,
    ]);
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 15,
    ]);

    $action = resolve(CancelPurchase::class);

    $cancelledPurchase = $action->handle($purchase, $user->id);

    expect($cancelledPurchase->status)->toBe(PurchaseStatusEnum::CANCELLED);

    // Check stock reversal created
    $stockMovements = StockMovement::query()
        ->where('source_type', Purchase::class)
        ->where('source_id', $purchase->id)
        ->get();

    expect($stockMovements)->toHaveCount(1)
        ->and($stockMovements->first()->quantity)->toBe(-15);
});

it('returns already cancelled purchase', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'status' => PurchaseStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelPurchase::class);
    $cancelledPurchase = $action->handle($purchase, $user->id);

    expect($cancelledPurchase->status)->toBe(PurchaseStatusEnum::CANCELLED);
});
