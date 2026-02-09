<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

test('to array', function (): void {
    $sale = Sale::factory()->create()->refresh();

    expect(array_keys($sale->toArray()))
        ->toBe([
            'id',
            'customer_id',
            'warehouse_id',
            'user_id',
            'reference_no',
            'status',
            'sale_date',
            'total_amount',
            'paid_amount',
            'change_amount',
            'payment_status',
            'note',
            'created_at',
            'updated_at',
        ]);
});

dataset('sale_belongs_to_relationships', [
    'customer' => fn (): array => ['relation' => 'customer', 'model' => Customer::class, 'foreignKey' => 'customer_id'],
    'warehouse' => fn (): array => ['relation' => 'warehouse', 'model' => Warehouse::class, 'foreignKey' => 'warehouse_id'],
    'user' => fn (): array => ['relation' => 'user', 'model' => User::class, 'foreignKey' => 'user_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $sale = new Sale();

    expect($sale->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('sale_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $sale = Sale::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($sale->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('sale_belongs_to_relationships');

it('has many items', function (): void {
    $sale = new Sale();

    expect($sale->items())
        ->toBeInstanceOf(HasMany::class);
});

it('can create items', function (): void {
    $sale = Sale::factory()->create();
    SaleItem::factory()->count(3)->create(['sale_id' => $sale->id]);

    expect($sale->items)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(SaleItem::class);
});

it('returns empty collection when no items exist', function (): void {
    $sale = Sale::factory()->create();

    expect($sale->items)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});

it('has many returns', function (): void {
    $sale = new Sale();

    expect($sale->returns())
        ->toBeInstanceOf(HasMany::class);
});

it('can create returns', function (): void {
    $sale = Sale::factory()->create();
    SaleReturn::factory()->count(2)->create(['sale_id' => $sale->id]);

    expect($sale->returns)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(SaleReturn::class);
});

it('has morphMany payments', function (): void {
    $sale = new Sale();

    expect($sale->payments())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create payments', function (): void {
    $sale = Sale::factory()->create();
    Payment::factory()->count(2)->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);

    expect($sale->payments)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Payment::class);
});

it('has morphMany stockMovements', function (): void {
    $sale = new Sale();

    expect($sale->stockMovements())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create stockMovements', function (): void {
    $sale = Sale::factory()->create();
    StockMovement::factory()->count(2)->create([
        'reference_type' => Sale::class,
        'reference_id' => $sale->id,
    ]);

    expect($sale->stockMovements)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(StockMovement::class);
});
