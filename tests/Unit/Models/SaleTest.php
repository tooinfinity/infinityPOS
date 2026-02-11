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

it('filters by pending scope', function (): void {
    Sale::factory()->create(['status' => 'pending']);
    Sale::factory()->count(2)->create(['status' => 'completed']);

    $results = Sale::pending()->get();

    expect($results)->toHaveCount(1)
        ->first()->status->value->toBe('pending');
});

it('filters by completed scope', function (): void {
    Sale::factory()->create(['status' => 'completed']);
    Sale::factory()->count(2)->create(['status' => 'pending']);

    $results = Sale::completed()->get();

    expect($results)->toHaveCount(1)
        ->first()->status->value->toBe('completed');
});

it('filters by cancelled scope', function (): void {
    Sale::factory()->create(['status' => 'cancelled']);
    Sale::factory()->count(2)->create(['status' => 'completed']);

    $results = Sale::cancelled()->get();

    expect($results)->toHaveCount(1)
        ->first()->status->value->toBe('cancelled');
});

it('filters by unpaid scope', function (): void {
    Sale::factory()->create(['payment_status' => 'unpaid']);
    Sale::factory()->count(2)->create(['payment_status' => 'paid']);

    $results = Sale::unpaid()->get();

    expect($results)->toHaveCount(1)
        ->first()->payment_status->value->toBe('unpaid');
});

it('filters by partially paid scope', function (): void {
    Sale::factory()->create(['payment_status' => 'partial']);
    Sale::factory()->count(2)->create(['payment_status' => 'paid']);

    $results = Sale::partiallyPaid()->get();

    expect($results)->toHaveCount(1)
        ->first()->payment_status->value->toBe('partial');
});

it('filters by paid scope', function (): void {
    Sale::factory()->create(['payment_status' => 'paid']);
    Sale::factory()->count(2)->create(['payment_status' => 'unpaid']);

    $results = Sale::paid()->get();

    expect($results)->toHaveCount(1)
        ->first()->payment_status->value->toBe('paid');
});

it('filters by today scope', function (): void {
    Sale::factory()->create(['sale_date' => now()]);
    Sale::factory()->create(['sale_date' => now()->subDay()]);
    Sale::factory()->create(['sale_date' => now()->addDay()]);

    $results = Sale::today()->get();

    expect($results)->toHaveCount(1);
});

it('calculates due amount accessor', function (): void {
    $sale = Sale::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 400,
    ]);

    expect($sale->due_amount)->toBe(600);
});

it('returns zero due amount when overpaid', function (): void {
    $sale = Sale::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 1200,
    ]);

    expect($sale->due_amount)->toBe(0);
});

it('calculates profit accessor from items', function (): void {
    $sale = Sale::factory()->create();
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'unit_price' => 100,
        'unit_cost' => 60,
        'quantity' => 2,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'unit_price' => 50,
        'unit_cost' => 30,
        'quantity' => 3,
    ]);

    $sale->refresh();

    expect($sale->profit)->toBe(140);
});

it('returns zero profit when no items exist', function (): void {
    $sale = Sale::factory()->create();

    expect($sale->profit)->toBe(0);
});
