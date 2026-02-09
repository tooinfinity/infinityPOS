<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $purchaseItem = PurchaseItem::factory()->create()->refresh();

    expect(array_keys($purchaseItem->toArray()))
        ->toBe([
            'id',
            'purchase_id',
            'product_id',
            'batch_id',
            'quantity',
            'received_quantity',
            'unit_cost',
            'subtotal',
            'expires_at',
            'created_at',
            'updated_at',
        ]);
});

dataset('purchase_item_belongs_to_relationships', [
    'purchase' => fn (): array => ['relation' => 'purchase', 'model' => Purchase::class, 'foreignKey' => 'purchase_id'],
    'product' => fn (): array => ['relation' => 'product', 'model' => Product::class, 'foreignKey' => 'product_id'],
    'batch' => fn (): array => ['relation' => 'batch', 'model' => Batch::class, 'foreignKey' => 'batch_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $purchaseItem = new PurchaseItem();

    expect($purchaseItem->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('purchase_item_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($purchaseItem->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('purchase_item_belongs_to_relationships');
