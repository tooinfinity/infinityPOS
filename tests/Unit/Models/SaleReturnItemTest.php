<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $saleReturnItem = SaleReturnItem::factory()->create()->refresh();

    expect(array_keys($saleReturnItem->toArray()))
        ->toBe([
            'id',
            'sale_return_id',
            'product_id',
            'batch_id',
            'quantity',
            'unit_price',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});

dataset('sale_return_item_belongs_to_relationships', [
    'saleReturn' => fn (): array => ['relation' => 'saleReturn', 'model' => SaleReturn::class, 'foreignKey' => 'sale_return_id'],
    'product' => fn (): array => ['relation' => 'product', 'model' => Product::class, 'foreignKey' => 'product_id'],
    'batch' => fn (): array => ['relation' => 'batch', 'model' => Batch::class, 'foreignKey' => 'batch_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $saleReturnItem = new SaleReturnItem();

    expect($saleReturnItem->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('sale_return_item_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $saleReturnItem = SaleReturnItem::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($saleReturnItem->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('sale_return_item_belongs_to_relationships');
