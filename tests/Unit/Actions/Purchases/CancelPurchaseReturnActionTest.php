<?php

declare(strict_types=1);

use App\Actions\Purchases\CancelPurchaseReturn;
use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\User;

it('may cancel a pending purchase return', function (): void {
    $user = User::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelPurchaseReturn::class);

    $cancelledReturn = $action->handle($purchaseReturn, $user->id);

    expect($cancelledReturn->status)->toBe(PurchaseReturnStatusEnum::CANCELLED)
        ->and($cancelledReturn->updated_by)->toBe($user->id);
});

it('may cancel a completed purchase return and reverse stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::COMPLETED,
        'created_by' => $user->id,
    ]);
    PurchaseReturnItem::factory()->create([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'quantity' => 6,
    ]);

    $action = resolve(CancelPurchaseReturn::class);

    $cancelledReturn = $action->handle($purchaseReturn, $user->id);

    expect($cancelledReturn->status)->toBe(PurchaseReturnStatusEnum::CANCELLED);

    // Check stock reversal created (positive quantity to restore stock)
    $stockMovements = StockMovement::query()
        ->where('source_type', PurchaseReturn::class)
        ->where('source_id', $purchaseReturn->id)
        ->get();

    expect($stockMovements)->toHaveCount(1)
        ->and($stockMovements->first()->quantity)->toBe(6);
});

it('returns already cancelled purchase return', function (): void {
    $user = User::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelPurchaseReturn::class);
    $cancelledReturn = $action->handle($purchaseReturn, $user->id);

    expect($cancelledReturn->status)->toBe(PurchaseReturnStatusEnum::CANCELLED);
});
