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
            'paid_amount',
            'payment_status',
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

it('filters by pending scope', function (): void {
    SaleReturn::factory()->create(['status' => 'pending']);
    SaleReturn::factory()->count(2)->create(['status' => 'completed']);

    $results = SaleReturn::pending()->get();

    expect($results)->toHaveCount(1)
        ->first()->status->value->toBe('pending');
});

it('filters by completed scope', function (): void {
    SaleReturn::factory()->create(['status' => 'completed']);
    SaleReturn::factory()->count(2)->create(['status' => 'pending']);

    $results = SaleReturn::completed()->get();

    expect($results)->toHaveCount(1)
        ->first()->status->value->toBe('completed');
});

it('filters by unpaid scope', function (): void {
    SaleReturn::factory()->unpaid()->create();
    SaleReturn::factory()->count(2)->paid()->create();

    $results = SaleReturn::unpaid()->get();

    expect($results)->toHaveCount(1)
        ->first()->payment_status->value->toBe('unpaid');
});

it('filters by partiallyPaid scope', function (): void {
    SaleReturn::factory()->partiallyPaid(1000)->create(['total_amount' => 1000]);
    SaleReturn::factory()->count(2)->paid()->create();

    $results = SaleReturn::partiallyPaid()->get();

    expect($results)->toHaveCount(1)
        ->first()->payment_status->value->toBe('partial');
});

it('filters by paid scope', function (): void {
    SaleReturn::factory()->paid()->create();
    SaleReturn::factory()->count(2)->unpaid()->create();

    $results = SaleReturn::paid()->get();

    expect($results)->toHaveCount(1)
        ->first()->payment_status->value->toBe('paid');
});

it('calculates due_amount accessor', function (): void {
    $unpaidReturn = SaleReturn::factory()->unpaid()->create(['total_amount' => 1000]);
    $partiallyPaidReturn = SaleReturn::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 300,
    ]);
    $paidReturn = SaleReturn::factory()->paid(1000)->create(['total_amount' => 1000]);

    expect($unpaidReturn->due_amount)->toBe(1000)
        ->and($partiallyPaidReturn->due_amount)->toBe(700)
        ->and($paidReturn->due_amount)->toBe(0);
});

it('filters by withDueAmount scope', function (): void {
    $saleReturnWithDue = SaleReturn::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 400,
    ]);

    $result = SaleReturn::withDueAmount()->find($saleReturnWithDue->id);

    expect($result->due_amount)->toBe(600);
});

it('returns zero due amount with scope when overpaid', function (): void {
    $saleReturnOverpaid = SaleReturn::factory()->create([
        'total_amount' => 1000,
        'paid_amount' => 1200,
    ]);

    $result = SaleReturn::withDueAmount()->find($saleReturnOverpaid->id);

    expect($result->due_amount)->toBe(0);
});
