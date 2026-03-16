<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $batch = Batch::factory()->create()->refresh();

    expect(array_keys($batch->toArray()))
        ->toBe([
            'id',
            'product_id',
            'warehouse_id',
            'batch_number',
            'cost_amount',
            'quantity',
            'expires_at',
            'created_at',
            'updated_at',
        ]);
});

dataset('batch_belongs_to_relationships', [
    'product' => fn (): array => ['relation' => 'product', 'model' => Product::class, 'foreignKey' => 'product_id'],
    'warehouse' => fn (): array => ['relation' => 'warehouse', 'model' => Warehouse::class, 'foreignKey' => 'warehouse_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $batch = new Batch();

    expect($batch->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('batch_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $batch = Batch::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($batch->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('batch_belongs_to_relationships');

dataset('batch_has_many_relationships', [
    'stockMovements' => fn (): array => ['relation' => 'stockMovements', 'model' => StockMovement::class],
    'purchaseItems' => fn (): array => ['relation' => 'purchaseItems', 'model' => PurchaseItem::class],
    'saleItems' => fn (): array => ['relation' => 'saleItems', 'model' => SaleItem::class],
    'stockTransferItems' => fn (): array => ['relation' => 'stockTransferItems', 'model' => StockTransferItem::class],
    'saleReturnItems' => fn (): array => ['relation' => 'saleReturnItems', 'model' => SaleReturnItem::class],
    'purchaseReturnItems' => fn (): array => ['relation' => 'purchaseReturnItems', 'model' => PurchaseReturnItem::class],
]);

it('has many {relation}', function (array $config): void {
    $batch = new Batch();

    expect($batch->{$config['relation']}())
        ->toBeInstanceOf(HasMany::class);
})->with('batch_has_many_relationships');

it('can create {relation}', function (array $config): void {
    $batch = Batch::factory()->create();
    $config['model']::factory()->count(3)->create(['batch_id' => $batch->id]);

    expect($batch->{$config['relation']})
        ->toHaveCount(3)
        ->each->toBeInstanceOf($config['model']);
})->with('batch_has_many_relationships');

it('returns empty collection when no {relation} exist', function (array $config): void {
    $batch = Batch::factory()->create();

    expect($batch->{$config['relation']})
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
})->with('batch_has_many_relationships');
