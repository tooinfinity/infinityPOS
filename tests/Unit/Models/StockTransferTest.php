<?php

declare(strict_types=1);

use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

test('to array', function (): void {
    $stockTransfer = StockTransfer::factory()->create()->refresh();

    expect(array_keys($stockTransfer->toArray()))
        ->toBe([
            'id',
            'from_warehouse_id',
            'to_warehouse_id',
            'user_id',
            'reference_no',
            'status',
            'note',
            'transfer_date',
            'created_at',
            'updated_at',
        ]);
});

it('belongs to fromWarehouse', function (): void {
    $stockTransfer = new StockTransfer();

    expect($stockTransfer->fromWarehouse())
        ->toBeInstanceOf(BelongsTo::class);
});

it('can access fromWarehouse', function (): void {
    $warehouse = Warehouse::factory()->create();
    $stockTransfer = StockTransfer::factory()->create([
        'from_warehouse_id' => $warehouse->id,
    ]);

    expect($stockTransfer->fromWarehouse)
        ->toBeInstanceOf(Warehouse::class)
        ->id->toBe($warehouse->id);
});

it('belongs to toWarehouse', function (): void {
    $stockTransfer = new StockTransfer();

    expect($stockTransfer->toWarehouse())
        ->toBeInstanceOf(BelongsTo::class);
});

it('can access toWarehouse', function (): void {
    $warehouse = Warehouse::factory()->create();
    $stockTransfer = StockTransfer::factory()->create([
        'to_warehouse_id' => $warehouse->id,
    ]);

    expect($stockTransfer->toWarehouse)
        ->toBeInstanceOf(Warehouse::class)
        ->id->toBe($warehouse->id);
});

it('belongs to user', function (): void {
    $stockTransfer = new StockTransfer();

    expect($stockTransfer->user())
        ->toBeInstanceOf(BelongsTo::class);
});

it('can access user', function (): void {
    $user = User::factory()->create();
    $stockTransfer = StockTransfer::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($stockTransfer->user)
        ->toBeInstanceOf(User::class)
        ->id->toBe($user->id);
});

it('has many items', function (): void {
    $stockTransfer = new StockTransfer();

    expect($stockTransfer->items())
        ->toBeInstanceOf(HasMany::class);
});

it('can create items', function (): void {
    $stockTransfer = StockTransfer::factory()->create();
    StockTransferItem::factory()->count(3)->create(['stock_transfer_id' => $stockTransfer->id]);

    expect($stockTransfer->items)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(StockTransferItem::class);
});

it('returns empty collection when no items exist', function (): void {
    $stockTransfer = StockTransfer::factory()->create();

    expect($stockTransfer->items)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});

it('has morphMany stockMovements', function (): void {
    $stockTransfer = new StockTransfer();

    expect($stockTransfer->stockMovements())
        ->toBeInstanceOf(MorphMany::class);
});

it('can create stockMovements', function (): void {
    $stockTransfer = StockTransfer::factory()->create();
    StockMovement::factory()->count(2)->create([
        'reference_type' => StockTransfer::class,
        'reference_id' => $stockTransfer->id,
    ]);

    expect($stockTransfer->stockMovements)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(StockMovement::class);
});
