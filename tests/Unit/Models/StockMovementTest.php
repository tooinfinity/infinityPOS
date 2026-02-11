<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

test('to array', function (): void {
    $stockMovement = StockMovement::factory()->create()->refresh();

    expect(array_keys($stockMovement->toArray()))
        ->toBe([
            'id',
            'warehouse_id',
            'product_id',
            'batch_id',
            'user_id',
            'type',
            'quantity',
            'previous_quantity',
            'current_quantity',
            'reference_type',
            'reference_id',
            'note',
            'created_at',
        ]);
});

dataset('stock_movement_belongs_to_relationships', [
    'warehouse' => fn (): array => ['relation' => 'warehouse', 'model' => Warehouse::class, 'foreignKey' => 'warehouse_id'],
    'product' => fn (): array => ['relation' => 'product', 'model' => Product::class, 'foreignKey' => 'product_id'],
    'batch' => fn (): array => ['relation' => 'batch', 'model' => Batch::class, 'foreignKey' => 'batch_id'],
    'user' => fn (): array => ['relation' => 'user', 'model' => User::class, 'foreignKey' => 'user_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $stockMovement = new StockMovement();

    expect($stockMovement->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('stock_movement_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $stockMovement = StockMovement::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($stockMovement->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('stock_movement_belongs_to_relationships');

it('has reference morphTo relationship', function (): void {
    $stockMovement = new StockMovement();

    expect($stockMovement->reference())
        ->toBeInstanceOf(MorphTo::class);
});

it('can access reference as Sale', function (): void {
    $sale = Sale::factory()->create();
    $stockMovement = StockMovement::factory()->create([
        'reference_type' => Sale::class,
        'reference_id' => $sale->id,
    ]);

    expect($stockMovement->reference)
        ->toBeInstanceOf(Sale::class)
        ->id->toBe($sale->id);
});

it('can access reference as Purchase', function (): void {
    $purchase = Purchase::factory()->create();
    $stockMovement = StockMovement::factory()->create([
        'reference_type' => Purchase::class,
        'reference_id' => $purchase->id,
    ]);

    expect($stockMovement->reference)
        ->toBeInstanceOf(Purchase::class)
        ->id->toBe($purchase->id);
});

it('filters by in scope', function (): void {
    StockMovement::factory()->create(['type' => 'in']);
    StockMovement::factory()->count(2)->create(['type' => 'out']);

    $results = StockMovement::in()->get();

    expect($results)->toHaveCount(1)
        ->first()->type->value->toBe('in');
});

it('filters by out scope', function (): void {
    StockMovement::factory()->create(['type' => 'out']);
    StockMovement::factory()->count(2)->create(['type' => 'in']);

    $results = StockMovement::out()->get();

    expect($results)->toHaveCount(1)
        ->first()->type->value->toBe('out');
});

it('filters by transfer scope', function (): void {
    StockMovement::factory()->create(['type' => 'transfer']);
    StockMovement::factory()->count(2)->create(['type' => 'in']);

    $results = StockMovement::transfer()->get();

    expect($results)->toHaveCount(1)
        ->first()->type->value->toBe('transfer');
});

it('filters by adjustment scope', function (): void {
    StockMovement::factory()->create(['type' => 'adjustment']);
    StockMovement::factory()->count(2)->create(['type' => 'in']);

    $results = StockMovement::adjustment()->get();

    expect($results)->toHaveCount(1)
        ->first()->type->value->toBe('adjustment');
});

it('filters by recent scope', function (): void {
    StockMovement::factory()->create(['created_at' => now()->subDays(10)]);
    StockMovement::factory()->create(['created_at' => now()->subDays(35)]);
    StockMovement::factory()->create(['created_at' => now()->subDays(5)]);

    $results = StockMovement::recent()->get();

    expect($results)->toHaveCount(2);
});

it('filters by recent scope with custom days', function (): void {
    StockMovement::factory()->create(['created_at' => now()->subDays(10)]);
    StockMovement::factory()->create(['created_at' => now()->subDays(20)]);

    $results = StockMovement::recent(15)->get();

    expect($results)->toHaveCount(1);
});
