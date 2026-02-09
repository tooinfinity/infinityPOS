<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $warehouse = Warehouse::factory()->create()->refresh();

    expect(array_keys($warehouse->toArray()))
        ->toBe([
            'id',
            'name',
            'code',
            'email',
            'phone',
            'address',
            'city',
            'country',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active warehouses by default', function (): void {
    Warehouse::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Warehouse::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $warehouses = Warehouse::all();

    expect($warehouses)
        ->toHaveCount(2);
});

dataset('warehouse_has_many_relationships', [
    'batches' => fn (): array => ['relation' => 'batches', 'model' => Batch::class, 'foreignKey' => 'warehouse_id'],
    'stockMovements' => fn (): array => ['relation' => 'stockMovements', 'model' => StockMovement::class, 'foreignKey' => 'warehouse_id'],
    'purchases' => fn (): array => ['relation' => 'purchases', 'model' => Purchase::class, 'foreignKey' => 'warehouse_id'],
    'sales' => fn (): array => ['relation' => 'sales', 'model' => Sale::class, 'foreignKey' => 'warehouse_id'],
    'saleReturns' => fn (): array => ['relation' => 'saleReturns', 'model' => SaleReturn::class, 'foreignKey' => 'warehouse_id'],
    'purchaseReturns' => fn (): array => ['relation' => 'purchaseReturns', 'model' => PurchaseReturn::class, 'foreignKey' => 'warehouse_id'],
]);

it('has many {relation}', function (array $config): void {
    $warehouse = new Warehouse();

    expect($warehouse->{$config['relation']}())
        ->toBeInstanceOf(HasMany::class);
})->with('warehouse_has_many_relationships');

it('can create {relation}', function (array $config): void {
    $warehouse = Warehouse::factory()->create();
    $config['model']::factory()->count(3)->create([$config['foreignKey'] => $warehouse->id]);

    expect($warehouse->{$config['relation']})
        ->toHaveCount(3)
        ->each->toBeInstanceOf($config['model']);
})->with('warehouse_has_many_relationships');

it('returns empty collection when no {relation} exist', function (array $config): void {
    $warehouse = Warehouse::factory()->create();

    expect($warehouse->{$config['relation']})
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
})->with('warehouse_has_many_relationships');

it('has many transfersFrom', function (): void {
    $warehouse = new Warehouse();

    expect($warehouse->transfersFrom())
        ->toBeInstanceOf(HasMany::class);
});

it('can create transfersFrom', function (): void {
    $warehouse = Warehouse::factory()->create();
    StockTransfer::factory()->count(2)->create(['from_warehouse_id' => $warehouse->id]);

    expect($warehouse->transfersFrom)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(StockTransfer::class);
});

it('has many transfersTo', function (): void {
    $warehouse = new Warehouse();

    expect($warehouse->transfersTo())
        ->toBeInstanceOf(HasMany::class);
});

it('can create transfersTo', function (): void {
    $warehouse = Warehouse::factory()->create();
    StockTransfer::factory()->count(2)->create(['to_warehouse_id' => $warehouse->id]);

    expect($warehouse->transfersTo)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(StockTransfer::class);
});
