<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

test('to array', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create()->refresh();

    expect(array_keys($purchaseReturn->toArray()))
        ->toBe([
            'id',
            'purchase_id',
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

dataset('purchase_return_belongs_to_relationships', [
    'purchase' => fn (): array => ['relation' => 'purchase', 'model' => Purchase::class, 'foreignKey' => 'purchase_id'],
    'warehouse' => fn (): array => ['relation' => 'warehouse', 'model' => Warehouse::class, 'foreignKey' => 'warehouse_id'],
    'user' => fn (): array => ['relation' => 'user', 'model' => User::class, 'foreignKey' => 'user_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $purchaseReturn = new PurchaseReturn();

    expect($purchaseReturn->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('purchase_return_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $purchaseReturn = PurchaseReturn::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($purchaseReturn->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('purchase_return_belongs_to_relationships');

it('has many items', function (): void {
    $purchaseReturn = new PurchaseReturn();

    expect($purchaseReturn->items())
        ->toBeInstanceOf(HasMany::class);
});

it('can create items', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create();
    PurchaseReturnItem::factory()->count(3)->create(['purchase_return_id' => $purchaseReturn->id]);

    expect($purchaseReturn->items)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(PurchaseReturnItem::class);
});

it('returns empty collection when no items exist', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create();

    expect($purchaseReturn->items)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});

it('has morphMany stockMovements', function (): void {
    $purchaseReturn = new PurchaseReturn();

    expect($purchaseReturn->stockMovements())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create stockMovements', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create();
    StockMovement::factory()->count(2)->create([
        'reference_type' => PurchaseReturn::class,
        'reference_id' => $purchaseReturn->id,
    ]);

    expect($purchaseReturn->stockMovements)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(StockMovement::class);
});

it('has morphMany payments', function (): void {
    $purchaseReturn = new PurchaseReturn();

    expect($purchaseReturn->payments())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create payments', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create();
    Payment::factory()->count(2)->create([
        'payable_type' => PurchaseReturn::class,
        'payable_id' => $purchaseReturn->id,
    ]);

    expect($purchaseReturn->payments)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Payment::class);
});

it('returns empty collection when no payments exist', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create();

    expect($purchaseReturn->payments)->toBeEmpty();
});

it('has morphMany activePayments', function (): void {
    $purchaseReturn = new PurchaseReturn();

    expect($purchaseReturn->activePayments())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create activePayments', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create();
    Payment::factory()->count(2)->create([
        'payable_type' => PurchaseReturn::class,
        'payable_id' => $purchaseReturn->id,
    ]);
    Payment::factory()->count(3)->voided()->create([
        'payable_type' => PurchaseReturn::class,
        'payable_id' => $purchaseReturn->id,
    ]);

    expect($purchaseReturn->activePayments)->toHaveCount(2);
});

it('returns empty collection when no activePayments exist', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create();

    expect($purchaseReturn->activePayments)->toBeEmpty();
});
