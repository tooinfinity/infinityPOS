<?php

declare(strict_types=1);

use App\Actions\Sales\CancelSaleReturn;
use App\Enums\SaleReturnStatusEnum;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\User;

it('may cancel a pending sale return', function (): void {
    $user = User::factory()->create();
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelSaleReturn::class);

    $cancelledReturn = $action->handle($saleReturn, $user->id);

    expect($cancelledReturn->status)->toBe(SaleReturnStatusEnum::CANCELLED)
        ->and($cancelledReturn->updated_by)->toBe($user->id);
});

it('may cancel a completed sale return and reverse stock movements', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::COMPLETED,
        'created_by' => $user->id,
    ]);
    SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    // Create initial stock movement (simulating completed return)
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $saleReturn->store_id,
        'quantity' => 5,
        'source_type' => SaleReturn::class,
        'source_id' => $saleReturn->id,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelSaleReturn::class);

    $cancelledReturn = $action->handle($saleReturn, $user->id);

    expect($cancelledReturn->status)->toBe(SaleReturnStatusEnum::CANCELLED);

    // Check reversal stock movement created
    $reversalMovement = StockMovement::query()
        ->where('source_type', SaleReturn::class)
        ->where('source_id', $saleReturn->id)
        ->where('quantity', -5)
        ->first();

    expect($reversalMovement)->not->toBeNull();
});

it('may not cancel a cancelled return sale', function (): void {
    $user = User::factory()->create();
    $saleReturn = SaleReturn::factory()->create([
        'status' => SaleReturnStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelSaleReturn::class);
    $cancelledReturn = $action->handle($saleReturn, $user->id);

    expect($cancelledReturn->status)->toBe(SaleReturnStatusEnum::CANCELLED);
});
