<?php

declare(strict_types=1);

use App\Actions\Sales\CancelSale;
use App\Enums\SaleStatusEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;

it('may cancel a pending sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelSale::class);

    $cancelledSale = $action->handle($sale, $user->id);

    expect($cancelledSale->status)->toBe(SaleStatusEnum::CANCELLED)
        ->and($cancelledSale->updated_by)->toBe($user->id);
});

it('may cancel a completed sale and reverse stock movements', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::COMPLETED,
        'created_by' => $user->id,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    // Create initial stock movement (simulating completed sale)
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $sale->store_id,
        'quantity' => -10,
        'source_type' => Sale::class,
        'source_id' => $sale->id,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelSale::class);

    $cancelledSale = $action->handle($sale, $user->id);

    expect($cancelledSale->status)->toBe(SaleStatusEnum::CANCELLED);

    // Check reversal stock movement created
    $reversalMovement = StockMovement::query()
        ->where('source_type', Sale::class)
        ->where('source_id', $sale->id)
        ->where('quantity', 10)
        ->first();

    expect($reversalMovement)->not->toBeNull();
});

it('may not cancel a cancelled sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CancelSale::class);
    $cancelledSale = $action->handle($sale, $user->id);

    expect($cancelledSale->status)->toBe(SaleStatusEnum::CANCELLED);
});
