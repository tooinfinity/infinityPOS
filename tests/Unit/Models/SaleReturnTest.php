<?php

declare(strict_types=1);

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

test('to array', function (): void {
    $saleReturn = SaleReturn::factory()->create()->refresh();

    expect(array_keys($saleReturn->toArray()))
        ->toBe([
            'id',
            'sale_id',
            'warehouse_id',
            'user_id',
            'reference_no',
            'return_date',
            'total_amount',
            'status',
            'note',
            'created_at',
            'updated_at',
        ]);
});

dataset('sale_return_belongs_to_relationships', [
    'sale' => fn (): array => ['relation' => 'sale', 'model' => Sale::class, 'foreignKey' => 'sale_id'],
    'warehouse' => fn (): array => ['relation' => 'warehouse', 'model' => Warehouse::class, 'foreignKey' => 'warehouse_id'],
    'user' => fn (): array => ['relation' => 'user', 'model' => User::class, 'foreignKey' => 'user_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('sale_return_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $saleReturn = SaleReturn::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($saleReturn->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('sale_return_belongs_to_relationships');

it('has many items', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->items())
        ->toBeInstanceOf(HasMany::class);
});

it('can create items', function (): void {
    $saleReturn = SaleReturn::factory()->create();
    SaleReturnItem::factory()->count(3)->create(['sale_return_id' => $saleReturn->id]);

    expect($saleReturn->items)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(SaleReturnItem::class);
});

it('returns empty collection when no items exist', function (): void {
    $saleReturn = SaleReturn::factory()->create();

    expect($saleReturn->items)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});

it('has morphMany stockMovements', function (): void {
    $saleReturn = new SaleReturn();

    expect($saleReturn->stockMovements())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create stockMovements', function (): void {
    $saleReturn = SaleReturn::factory()->create();
    StockMovement::factory()->count(2)->create([
        'reference_type' => SaleReturn::class,
        'reference_id' => $saleReturn->id,
    ]);

    expect($saleReturn->stockMovements)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(StockMovement::class);
});
