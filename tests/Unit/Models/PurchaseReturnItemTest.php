<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $purchaseReturnItem = PurchaseReturnItem::factory()->create()->refresh();

    expect(array_keys($purchaseReturnItem->toArray()))
        ->toBe([
            'id',
            'purchase_return_id',
            'product_id',
            'batch_id',
            'quantity',
            'unit_cost',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});

dataset('purchase_return_item_belongs_to_relationships', [
    'purchaseReturn' => fn (): array => ['relation' => 'purchaseReturn', 'model' => PurchaseReturn::class, 'foreignKey' => 'purchase_return_id'],
    'product' => fn (): array => ['relation' => 'product', 'model' => Product::class, 'foreignKey' => 'product_id'],
    'batch' => fn (): array => ['relation' => 'batch', 'model' => Batch::class, 'foreignKey' => 'batch_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $purchaseReturnItem = new PurchaseReturnItem();

    expect($purchaseReturnItem->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('purchase_return_item_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $purchaseReturnItem = PurchaseReturnItem::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($purchaseReturnItem->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('purchase_return_item_belongs_to_relationships');
