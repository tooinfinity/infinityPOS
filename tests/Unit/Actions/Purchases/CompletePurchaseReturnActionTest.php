<?php

declare(strict_types=1);

use App\Actions\Purchases\CompletePurchaseReturn;
use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\User;

it('may complete a pending purchase return', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);
    PurchaseReturnItem::factory()->create([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'quantity' => 8,
    ]);

    $action = resolve(CompletePurchaseReturn::class);

    $completedReturn = $action->handle($purchaseReturn, $user->id);

    expect($completedReturn->status)->toBe(PurchaseReturnStatusEnum::COMPLETED)
        ->and($completedReturn->updated_by)->toBe($user->id);

    // Check stock movements created (negative quantity for return)
    $stockMovement = StockMovement::query()
        ->where('source_type', PurchaseReturn::class)
        ->where('source_id', $purchaseReturn->id)
        ->first();

    expect($stockMovement)->not->toBeNull()
        ->and($stockMovement->quantity)->toBe(-8)
        ->and($stockMovement->product_id)->toBe($product->id);
});

it('cannot complete a cancelled purchase return', function (): void {
    $user = User::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CompletePurchaseReturn::class);

    $action->handle($purchaseReturn, $user->id);
})->throws(InvalidArgumentException::class, 'Cannot complete a cancelled purchase return.');

it('returns already completed purchase return', function (): void {
    $user = User::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->create([
        'status' => PurchaseReturnStatusEnum::COMPLETED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CompletePurchaseReturn::class);
    $completedReturn = $action->handle($purchaseReturn, $user->id);

    expect($completedReturn->status)->toBe(PurchaseReturnStatusEnum::COMPLETED);
});
