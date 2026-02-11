<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $saleItem = SaleItem::factory()->create()->refresh();

    expect(array_keys($saleItem->toArray()))
        ->toBe([
            'id',
            'sale_id',
            'product_id',
            'batch_id',
            'quantity',
            'unit_price',
            'unit_cost',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});

dataset('sale_item_belongs_to_relationships', [
    'sale' => fn (): array => ['relation' => 'sale', 'model' => Sale::class, 'foreignKey' => 'sale_id'],
    'product' => fn (): array => ['relation' => 'product', 'model' => Product::class, 'foreignKey' => 'product_id'],
    'batch' => fn (): array => ['relation' => 'batch', 'model' => Batch::class, 'foreignKey' => 'batch_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $saleItem = new SaleItem();

    expect($saleItem->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('sale_item_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $saleItem = SaleItem::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($saleItem->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('sale_item_belongs_to_relationships');

it('calculates profit accessor', function (): void {
    $saleItem = SaleItem::factory()->create([
        'unit_price' => 100,
        'unit_cost' => 60,
        'quantity' => 5,
    ]);

    expect($saleItem->profit)->toBe(200); // (100 - 60) * 5 = 200
});

it('calculates profit accessor with zero cost', function (): void {
    $saleItem = SaleItem::factory()->create([
        'unit_price' => 100,
        'unit_cost' => 0,
        'quantity' => 3,
    ]);

    expect($saleItem->profit)->toBe(300); // (100 - 0) * 3 = 300
});

it('calculates profit accessor with loss', function (): void {
    $saleItem = SaleItem::factory()->create([
        'unit_price' => 50,
        'unit_cost' => 70,
        'quantity' => 2,
    ]);

    expect($saleItem->profit)->toBe(-40); // (50 - 70) * 2 = -40
});
