<?php

declare(strict_types=1);

use App\Actions\Sales\CompleteSale;
use App\Enums\SaleStatusEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;

it('may complete a pending sale', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteSale::class);

    $completedSale = $action->handle($sale, $user->id);

    expect($completedSale->status)->toBe(SaleStatusEnum::COMPLETED)
        ->and($completedSale->updated_by)->toBe($user->id);

    // Check stock movements created
    $stockMovement = StockMovement::query()
        ->where('source_type', Sale::class)
        ->where('source_id', $sale->id)
        ->first();

    expect($stockMovement)->not->toBeNull()
        ->and($stockMovement->quantity)->toBe(-10)
        ->and($stockMovement->product_id)->toBe($product->id);
});

it('cannot complete a cancelled sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::CANCELLED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CompleteSale::class);

    $action->handle($sale, $user->id);
})->throws(InvalidArgumentException::class, 'Cannot complete a cancelled sale.');

it('cannot complete a completed sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::COMPLETED,
        'created_by' => $user->id,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale, $user->id);

    expect($sale->status)->toBe(SaleStatusEnum::COMPLETED);

});
