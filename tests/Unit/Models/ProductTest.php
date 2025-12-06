<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $product = Product::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($product->toArray()))
        ->toBe([
            'id',
            'sku',
            'barcode',
            'name',
            'description',
            'image',
            'cost',
            'price',
            'alert_quantity',
            'has_batches',
            'is_active',
            'category_id',
            'brand_id',
            'unit_id',
            'tax_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('product relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $category = Category::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $client = Client::factory()->create(['created_by' => $user->id]);
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $brand = Brand::factory()->create(['created_by' => $user->id]);
    $unit = Unit::factory()->create(['created_by' => $user->id]);
    $tax = Tax::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
        'tax_id' => $tax->id,
        'created_by' => $user->id]
    );
    $sale = Sale::factory()->create(['client_id' => $client->id, 'store_id' => $store->id, 'created_by' => $user->id]);
    $saleItems = SaleItem::factory()->create(['sale_id' => $sale->id, 'product_id' => $product->id]);
    $purchase = Purchase::factory()->create(['supplier_id' => $supplier->id, 'store_id' => $store->id, 'created_by' => $user->id]);
    $purchaseItems = PurchaseItem::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $product->id]);
    $product->update(['updated_by' => $user->id]);

    // todo: stores stoke relationship test

    expect($product->creator->id)->toBe($user->id)
        ->and($product->updater->id)->toBe($user->id)
        ->and($product->category->id)->toBe($category->id)
        ->and($product->brand->id)->toBe($brand->id)
        ->and($product->unit->id)->toBe($unit->id)
        ->and($product->tax->id)->toBe($tax->id)
        ->and($product->saleItems->count())->toBe(1)
        ->and($product->saleItems->first()->id)->toBe($saleItems->id)
        ->and($product->purchaseItems->count())->toBe(1)
        ->and($product->purchaseItems->first()->id)->toBe($purchaseItems->id);
});

test('has stores relationship through pivot', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    $product->stores()->attach($store->id, ['quantity' => 50]);

    expect($product->stores)->toHaveCount(1)
        ->and($product->stores->first()->id)->toBe($store->id)
        ->and($product->stores->first()->pivot->quantity)->toBe(50);
});
