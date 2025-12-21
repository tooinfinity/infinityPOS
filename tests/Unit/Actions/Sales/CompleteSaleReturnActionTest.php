<?php

declare(strict_types=1);

use App\Actions\Sales\CompleteSaleReturn;
use App\Enums\SaleReturnStatusEnum;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\User;

it('may complete a pending sale return', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);
    SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $action = resolve(CompleteSaleReturn::class);

    $completedReturn = $action->handle($saleReturn, $user->id);

    expect($completedReturn->status)->toBe(SaleReturnStatusEnum::COMPLETED)
        ->and($completedReturn->updated_by)->toBe($user->id);

    // Check stock movements created (positive quantity for returns)
    $stockMovement = StockMovement::query()
        ->where('source_type', SaleReturn::class)
        ->where('source_id', $saleReturn->id)
        ->first();

    expect($stockMovement)->not->toBeNull()
        ->and($stockMovement->quantity)->toBe(5)
        ->and($stockMovement->product_id)->toBe($product->id);
});

it('cannot complete a cancelled sale return', function (): void {
    $user = User::factory()->create();
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CompleteSaleReturn::class);

    $action->handle($saleReturn, $user->id);
})->throws(InvalidArgumentException::class, 'Cannot complete a cancelled sale return.');

it('cannot complete a completed sale return', function (): void {
    $user = User::factory()->create();
    $sale = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::COMPLETED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CompleteSaleReturn::class);
    $action->handle($sale, $user->id);

    expect($sale->status)->toBe(SaleReturnStatusEnum::COMPLETED);

});
