<?php

declare(strict_types=1);

use App\Collections\ProductCollection;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $product = Product::factory()->create()->refresh();

    expect(array_keys($product->toArray()))
        ->toBe([
            'id',
            'category_id',
            'name',
            'sku',
            'barcode',
            'description',
            'unit',
            'selling_price',
            'alert_quantity',
            'image',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('new collection returns product collection', function (): void {
    $product = new Product();

    expect($product->newCollection([]))
        ->toBeInstanceOf(ProductCollection::class);
});

test('category relationship returns belongs to', function (): void {
    $product = new Product();

    expect($product->category())
        ->toBeInstanceOf(BelongsTo::class);
});

test('inventory relationship returns has many', function (): void {
    $product = new Product();

    expect($product->inventory())
        ->toBeInstanceOf(HasMany::class);
});

test('inventory batches relationship returns has many', function (): void {
    $product = new Product();

    expect($product->inventoryBatches())
        ->toBeInstanceOf(HasMany::class);
});

test('purchase items relationship returns has many', function (): void {
    $product = new Product();

    expect($product->purchaseItems())
        ->toBeInstanceOf(HasMany::class);
});

test('sale items relationship returns has many', function (): void {
    $product = new Product();

    expect($product->saleItems())
        ->toBeInstanceOf(HasMany::class);
});

test('invoice items relationship returns has many', function (): void {
    $product = new Product();

    expect($product->invoiceItems())
        ->toBeInstanceOf(HasMany::class);
});

test('return items relationship returns has many', function (): void {
    $product = new Product();

    expect($product->returnItems())
        ->toBeInstanceOf(HasMany::class);
});

test('stock adjustments relationship returns has many', function (): void {
    $product = new Product();

    expect($product->stockAdjustments())
        ->toBeInstanceOf(HasMany::class);
});

test('is low stock returns true when inventory is at alert quantity', function (): void {
    $product = Product::factory()->create(['alert_quantity' => 10]);
    $store = App\Models\Store::factory()->create();
    Inventory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'total_quantity' => 10,
    ]);

    expect($product->isLowStock($store->id))->toBeTrue();
});

test('is low stock returns true when inventory is below alert quantity', function (): void {
    $product = Product::factory()->create(['alert_quantity' => 10]);
    $store = App\Models\Store::factory()->create();
    Inventory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'total_quantity' => 5,
    ]);

    expect($product->isLowStock($store->id))->toBeTrue();
});

test('is low stock returns false when inventory is above alert quantity', function (): void {
    $product = Product::factory()->create(['alert_quantity' => 10]);
    $store = App\Models\Store::factory()->create();
    Inventory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'total_quantity' => 20,
    ]);

    expect($product->isLowStock($store->id))->toBeFalse();
});

test('is low stock returns false when no inventory exists', function (): void {
    $product = Product::factory()->create(['alert_quantity' => 10]);
    $store = App\Models\Store::factory()->create();

    expect($product->isLowStock($store->id))->toBeFalse();
});

test('casts returns correct array', function (): void {
    $product = new Product();

    expect($product->casts())
        ->toBe([
            'id' => 'integer',
            'category_id' => 'integer',
            'name' => 'string',
            'sku' => 'string',
            'barcode' => 'string',
            'description' => 'string',
            'unit' => App\Enums\ProductUnitEnum::class,
            'selling_price' => 'integer',
            'alert_quantity' => 'integer',
            'image' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $product = Product::factory()->create(['is_active' => true])->refresh();

    expect($product->id)->toBeInt()
        ->and($product->selling_price)->toBeInt()
        ->and($product->is_active)->toBeTrue()
        ->and($product->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts unit to ProductUnitEnum', function (): void {
    $product = Product::factory()->create([
        'unit' => App\Enums\ProductUnitEnum::PIECE,
    ]);

    expect($product->unit)->toBeInstanceOf(App\Enums\ProductUnitEnum::class)
        ->and($product->unit)->toBe(App\Enums\ProductUnitEnum::PIECE);
});

test('can set unit using enum value', function (): void {
    $product = Product::factory()->create([
        'unit' => 'gram',
    ]);

    expect($product->unit)->toBeInstanceOf(App\Enums\ProductUnitEnum::class)
        ->and($product->unit->value)->toBe('gram');
});

test('can access enum methods on unit', function (): void {
    $product = Product::factory()->create([
        'unit' => App\Enums\ProductUnitEnum::MILLILITER,
    ]);

    expect($product->unit->label())->toBe('Milliliter (ml)')
        ->and($product->unit->abbreviation())->toBe('ml')
        ->and($product->unit->displayUnit())->toBe('L')
        ->and($product->unit->requiresDecimalInput())->toBeTrue();
});
