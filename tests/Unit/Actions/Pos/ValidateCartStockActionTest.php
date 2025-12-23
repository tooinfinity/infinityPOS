<?php

declare(strict_types=1);

use App\Actions\Pos\ValidateCartStock;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Settings\InventorySettings;

test('it passes validation when stock is sufficient', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    // Create inventory layer with sufficient stock
    App\Models\InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
    ]);

    $sale = Sale::factory()->create(['store_id' => $store->id]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $settings = resolve(InventorySettings::class);
    $settings->auto_deduct_stock = true;
    $settings->save();

    $action = new ValidateCartStock($settings);

    // Should not throw exception
    $action->handle($sale->load('items.product'), $store->id);

    expect(true)->toBeTrue();
});

test('it skips validation when auto_deduct_stock is disabled', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    $sale = Sale::factory()->create(['store_id' => $store->id]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 999999,
    ]);

    $settings = resolve(InventorySettings::class);
    $settings->auto_deduct_stock = false;
    $settings->save();

    $action = new ValidateCartStock($settings);

    // Should not throw exception even with insufficient stock
    $action->handle($sale->load('items.product'), $store->id);

    expect(true)->toBeTrue();
});
