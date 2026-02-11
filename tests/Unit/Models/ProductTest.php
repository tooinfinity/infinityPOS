<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\StockTransferItem;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

test('to array', function (): void {
    $product = Product::factory()->create()->refresh();

    expect(array_keys($product->toArray()))
        ->toBe([
            'id',
            'category_id',
            'brand_id',
            'unit_id',
            'name',
            'sku',
            'barcode',
            'description',
            'image',
            'cost_price',
            'selling_price',
            'quantity',
            'alert_quantity',
            'track_inventory',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active products by default', function (): void {
    Product::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Product::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $products = Product::all();

    expect($products)
        ->toHaveCount(2);
});

dataset('product_relationships', [
    'category' => fn (): array => ['relation' => 'category', 'model' => Category::class],
    'brand' => fn (): array => ['relation' => 'brand', 'model' => Brand::class],
    'unit' => fn (): array => ['relation' => 'unit', 'model' => Unit::class],
]);

it('belongs to {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $product = Product::factory()->create([
        $config['relation'].'_id' => $related->id,
    ]);

    expect($product->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('product_relationships');

dataset('has_many_relationships', [
    'batches' => fn (): array => ['relation' => 'batches', 'model' => Batch::class],
    'stockMovements' => fn (): array => ['relation' => 'stockMovements', 'model' => StockMovement::class],
    'purchaseItems' => fn (): array => ['relation' => 'purchaseItems', 'model' => PurchaseItem::class],
    'saleItems' => fn (): array => ['relation' => 'saleItems', 'model' => SaleItem::class],
    'stockTransferItems' => fn (): array => ['relation' => 'stockTransferItems', 'model' => StockTransferItem::class],
    'saleReturnItems' => fn (): array => ['relation' => 'saleReturnItems', 'model' => SaleReturnItem::class],
    'purchaseReturnItems' => fn (): array => ['relation' => 'purchaseReturnItems', 'model' => PurchaseReturnItem::class],
]);

it('has many {relation}', function (array $config): void {
    $product = new Product();

    expect($product->{$config['relation']}())
        ->toBeInstanceOf(HasMany::class);
})->with('has_many_relationships');

it('can create {relation}', function (array $config): void {
    $product = Product::factory()->create();
    $related = $config['model']::factory()->count(3)->create(['product_id' => $product->id]);

    expect($product->{$config['relation']})
        ->toHaveCount(3)
        ->each->toBeInstanceOf($config['model']);
})->with('has_many_relationships');

// Test empty relationship
it('returns empty collection when no {relation} exist', function (array $config): void {
    $product = Product::factory()->create();

    expect($product->{$config['relation']})
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
})->with('has_many_relationships');

it('can eager load {relation}', function (array $config): void {
    $product = Product::factory()->create();
    $config['model']::factory()->count(2)->create(['product_id' => $product->id]);

    $loadedProduct = Product::with($config['relation'])->find($product->id);

    expect($loadedProduct->relationLoaded($config['relation']))->toBeTrue()
        ->and($loadedProduct->{$config['relation']})->toHaveCount(2);
})->with('has_many_relationships');

it('counts {relation} correctly', function (array $config): void {
    $product = Product::factory()->create();
    $config['model']::factory()->count(5)->create(['product_id' => $product->id]);

    $productWithCount = Product::query()->withCount($config['relation'])->find($product->id);

    expect($productWithCount->{Str::snake($config['relation']).'_count'})->toBe(5);
})->with('has_many_relationships');

it('filters by low stock scope', function (): void {
    Product::factory()->create([
        'quantity' => 5,
        'alert_quantity' => 10,
        'track_inventory' => true,
    ]);
    Product::factory()->create([
        'quantity' => 15,
        'alert_quantity' => 10,
        'track_inventory' => true,
    ]);
    Product::factory()->create([
        'quantity' => 5,
        'alert_quantity' => 10,
        'track_inventory' => false,
    ]);

    $results = Product::lowStock()->get();

    expect($results)->toHaveCount(1)
        ->first()->quantity->toBe(5);
});

it('filters by out of stock scope', function (): void {
    Product::factory()->create([
        'quantity' => 0,
        'track_inventory' => true,
    ]);
    Product::factory()->create([
        'quantity' => 5,
        'track_inventory' => true,
    ]);
    Product::factory()->create([
        'quantity' => 0,
        'track_inventory' => false,
    ]);

    $results = Product::outOfStock()->get();

    expect($results)->toHaveCount(1)
        ->first()->quantity->toBe(0);
});

it('filters by search scope', function (): void {
    Product::factory()->create(['name' => 'Test Product', 'sku' => 'ABC123', 'barcode' => '123456']);
    Product::factory()->create(['name' => 'Another Product', 'sku' => 'XYZ789', 'barcode' => '789012']);

    expect(Product::search('Test')->get())->toHaveCount(1)
        ->and(Product::search('ABC123')->get())->toHaveCount(1)
        ->and(Product::search('123456')->get())->toHaveCount(1)
        ->and(Product::search('Product')->get())->toHaveCount(2)
        ->and(Product::search('NonExistent')->get())->toHaveCount(0);
});

it('filters by tracked scope', function (): void {
    Product::factory()->create(['track_inventory' => true]);
    Product::factory()->create(['track_inventory' => false]);

    $results = Product::tracked()->get();

    expect($results)->toHaveCount(1)
        ->first()->track_inventory->toBeTrue();
});

it('calculates is low stock accessor', function (): void {
    $lowStockProduct = Product::factory()->create([
        'quantity' => 5,
        'alert_quantity' => 10,
        'track_inventory' => true,
    ]);

    $normalStockProduct = Product::factory()->create([
        'quantity' => 15,
        'alert_quantity' => 10,
        'track_inventory' => true,
    ]);

    $notTrackedProduct = Product::factory()->create([
        'quantity' => 5,
        'alert_quantity' => 10,
        'track_inventory' => false,
    ]);

    expect($lowStockProduct->is_low_stock)->toBeTrue()
        ->and($normalStockProduct->is_low_stock)->toBeFalse()
        ->and($notTrackedProduct->is_low_stock)->toBeFalse();
});

it('calculates is out of stock accessor', function (): void {
    $outOfStockProduct = Product::factory()->create([
        'quantity' => 0,
        'track_inventory' => true,
    ]);

    $inStockProduct = Product::factory()->create([
        'quantity' => 5,
        'track_inventory' => true,
    ]);

    $notTrackedProduct = Product::factory()->create([
        'quantity' => 0,
        'track_inventory' => false,
    ]);

    expect($outOfStockProduct->is_out_of_stock)->toBeTrue()
        ->and($inStockProduct->is_out_of_stock)->toBeFalse()
        ->and($notTrackedProduct->is_out_of_stock)->toBeFalse();
});

it('calculates profit margin accessor', function (): void {
    $product = Product::factory()->create([
        'cost_price' => 60,
        'selling_price' => 100,
    ]);

    expect($product->profit_margin)->toBe(40.0);
});

it('returns zero profit margin when selling price is zero', function (): void {
    $product = Product::factory()->create([
        'cost_price' => 60,
        'selling_price' => 0,
    ]);

    expect($product->profit_margin)->toBe(0);
});
