<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $stockTransferItem = StockTransferItem::factory()->create()->refresh();

    expect(array_keys($stockTransferItem->toArray()))
        ->toBe([
            'id',
            'stock_transfer_id',
            'product_id',
            'batch_id',
            'quantity',
            'created_at',
            'updated_at',
        ]);
});

dataset('stock_transfer_item_belongs_to_relationships', [
    'stockTransfer' => fn (): array => ['relation' => 'stockTransfer', 'model' => StockTransfer::class, 'foreignKey' => 'stock_transfer_id'],
    'product' => fn (): array => ['relation' => 'product', 'model' => Product::class, 'foreignKey' => 'product_id'],
    'batch' => fn (): array => ['relation' => 'batch', 'model' => Batch::class, 'foreignKey' => 'batch_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $stockTransferItem = new StockTransferItem();

    expect($stockTransferItem->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('stock_transfer_item_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $stockTransferItem = StockTransferItem::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($stockTransferItem->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('stock_transfer_item_belongs_to_relationships');
