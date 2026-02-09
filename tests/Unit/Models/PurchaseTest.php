<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

test('to array', function (): void {
    $purchase = Purchase::factory()->create()->refresh();

    expect(array_keys($purchase->toArray()))
        ->toBe([
            'id',
            'supplier_id',
            'warehouse_id',
            'user_id',
            'reference_no',
            'status',
            'purchase_date',
            'total_amount',
            'paid_amount',
            'payment_status',
            'note',
            'document',
            'created_at',
            'updated_at',
        ]);
});

dataset('purchase_belongs_to_relationships', [
    'supplier' => fn (): array => ['relation' => 'supplier', 'model' => Supplier::class, 'foreignKey' => 'supplier_id'],
    'warehouse' => fn (): array => ['relation' => 'warehouse', 'model' => Warehouse::class, 'foreignKey' => 'warehouse_id'],
    'user' => fn (): array => ['relation' => 'user', 'model' => User::class, 'foreignKey' => 'user_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $purchase = new Purchase();

    expect($purchase->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('purchase_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $purchase = Purchase::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($purchase->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('purchase_belongs_to_relationships');

it('has many items', function (): void {
    $purchase = new Purchase();

    expect($purchase->items())
        ->toBeInstanceOf(HasMany::class);
});

it('can create items', function (): void {
    $purchase = Purchase::factory()->create();
    PurchaseItem::factory()->count(3)->create(['purchase_id' => $purchase->id]);

    expect($purchase->items)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(PurchaseItem::class);
});

it('returns empty collection when no items exist', function (): void {
    $purchase = Purchase::factory()->create();

    expect($purchase->items)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});

it('has many returns', function (): void {
    $purchase = new Purchase();

    expect($purchase->returns())
        ->toBeInstanceOf(HasMany::class);
});

it('can create returns', function (): void {
    $purchase = Purchase::factory()->create();
    PurchaseReturn::factory()->count(2)->create(['purchase_id' => $purchase->id]);

    expect($purchase->returns)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(PurchaseReturn::class);
});

it('has morphMany payments', function (): void {
    $purchase = new Purchase();

    expect($purchase->payments())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create payments', function (): void {
    $purchase = Purchase::factory()->create();
    Payment::factory()->count(2)->create([
        'payable_type' => Purchase::class,
        'payable_id' => $purchase->id,
    ]);

    expect($purchase->payments)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Payment::class);
});

it('has morphMany stockMovements', function (): void {
    $purchase = new Purchase();

    expect($purchase->stockMovements())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create stockMovements', function (): void {
    $purchase = Purchase::factory()->create();
    StockMovement::factory()->count(2)->create([
        'reference_type' => Purchase::class,
        'reference_id' => $purchase->id,
    ]);

    expect($purchase->stockMovements)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(StockMovement::class);
});
