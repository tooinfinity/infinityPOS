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

it('filters by in stock scope', function (): void {
    Batch::factory()->create(['quantity' => 10]);
    Batch::factory()->create(['quantity' => 0]);
    Batch::factory()->create(['quantity' => -5]);

    $results = Batch::inStock()->get();

    expect($results)->toHaveCount(1)
        ->first()->quantity->toBe(10);
});

it('filters by expired scope', function (): void {
    Batch::factory()->create(['expires_at' => now()->subDay()]);
    Batch::factory()->create(['expires_at' => now()->addDay()]);
    Batch::factory()->create(['expires_at' => null]);

    $results = Batch::expired()->get();

    expect($results)->toHaveCount(1)
        ->first()->expires_at->isPast()->toBeTrue();
});

it('filters by expiring soon scope', function (): void {
    Batch::factory()->create(['expires_at' => now()->addDays(15)]);
    Batch::factory()->create(['expires_at' => now()->addDays(35)]);
    Batch::factory()->create(['expires_at' => now()->subDay()]);
    Batch::factory()->create(['expires_at' => null]);

    $results = Batch::expiringSoon()->get();

    expect($results)->toHaveCount(1);
});

it('filters by expiring soon scope with custom days', function (): void {
    Batch::factory()->create(['expires_at' => now()->addDays(10)]);
    Batch::factory()->create(['expires_at' => now()->addDays(20)]);

    $results = Batch::expiringSoon(15)->get();

    expect($results)->toHaveCount(1);
});

it('orders by fifo scope', function (): void {
    $batch1 = Batch::factory()->create(['created_at' => now()->subDays(2)]);
    $batch2 = Batch::factory()->create(['created_at' => now()->subDay()]);
    $batch3 = Batch::factory()->create(['created_at' => now()]);

    $results = Batch::fifo()->get();

    expect($results->pluck('id')->toArray())->toBe([$batch1->id, $batch2->id, $batch3->id]);
});

it('orders by fefo scope', function (): void {
    $batch1 = Batch::factory()->create(['expires_at' => now()->addDays(5)]);
    $batch2 = Batch::factory()->create(['expires_at' => now()->addDays(10)]);
    $batch3 = Batch::factory()->create(['expires_at' => null]);

    $results = Batch::fefo()->get();

    expect($results->pluck('id')->toArray())->toBe([$batch1->id, $batch2->id, $batch3->id]);
});

it('calculates is expired accessor', function (): void {
    $expiredBatch = Batch::factory()->create(['expires_at' => now()->subDay()]);
    $validBatch = Batch::factory()->create(['expires_at' => now()->addDay()]);
    $noExpiryBatch = Batch::factory()->create(['expires_at' => null]);

    expect($expiredBatch->is_expired)->toBeTrue()
        ->and($validBatch->is_expired)->toBeFalse()
        ->and($noExpiryBatch->is_expired)->toBeFalse();
});

it('calculates is expiring soon accessor', function (): void {
    $expiringSoonBatch = Batch::factory()->create(['expires_at' => now()->addDays(15)]);
    $notExpiringSoonBatch = Batch::factory()->create(['expires_at' => now()->addDays(35)]);
    $expiredBatch = Batch::factory()->create(['expires_at' => now()->subDay()]);
    $noExpiryBatch = Batch::factory()->create(['expires_at' => null]);

    expect($expiringSoonBatch->is_expiring_soon)->toBeTrue()
        ->and($notExpiringSoonBatch->is_expiring_soon)->toBeFalse()
        ->and($expiredBatch->is_expiring_soon)->toBeFalse()
        ->and($noExpiryBatch->is_expiring_soon)->toBeFalse();
});
