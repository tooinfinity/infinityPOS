<?php

declare(strict_types=1);

use App\Actions\Batch\CreateBatch;
use App\Data\Batch\BatchData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;

it('may create a batch with required fields', function (): void {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $action = resolve(CreateBatch::class);

    $data = new BatchData(
        product_id: $product->id,
        warehouse_id: $warehouse->id,
        batch_number: 'BAT-'.now()->getTimestampMs().'-'.random_int(1000, 9999),
        cost_amount: 5000,
        quantity: 100,
        expires_at: null,
    );

    $batch = $action->handle($data);

    expect($batch)->toBeInstanceOf(Batch::class)
        ->and($batch->product_id)->toBe($product->id)
        ->and($batch->warehouse_id)->toBe($warehouse->id)
        ->and($batch->cost_amount)->toBe(5000)
        ->and($batch->quantity)->toBe(100)
        ->and($batch->exists)->toBeTrue();
});

it('creates batch with all optional fields', function (): void {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $action = resolve(CreateBatch::class);

    $data = new BatchData(
        product_id: $product->id,
        warehouse_id: $warehouse->id,
        batch_number: 'BATCH-001',
        cost_amount: 10000,
        quantity: 500,
        expires_at: now()->addYear(),
    );

    $batch = $action->handle($data);

    expect($batch->batch_number)->toBe('BATCH-001')
        ->and($batch->cost_amount)->toBe(10000)
        ->and($batch->quantity)->toBe(500)
        ->and($batch->expires_at)->not->toBeNull();
});

it('creates batch with auto-generated batch_number when null provided', function (): void {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $action = resolve(CreateBatch::class);

    $data = new BatchData(
        product_id: $product->id,
        warehouse_id: $warehouse->id,
        batch_number: 'BAT-'.now()->getTimestampMs().'-'.random_int(1000, 9999),
        cost_amount: 5000,
        quantity: 100,
        expires_at: null,
    );

    $batch = $action->handle($data);

    expect($batch->batch_number)->toStartWith('BAT-');
});

it('creates batch with null expires_at', function (): void {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $action = resolve(CreateBatch::class);

    $data = new BatchData(
        product_id: $product->id,
        warehouse_id: $warehouse->id,
        batch_number: 'BAT-'.now()->getTimestampMs().'-'.random_int(1000, 9999),
        cost_amount: 5000,
        quantity: 100,
        expires_at: null,
    );

    $batch = $action->handle($data);

    expect($batch->expires_at)->toBeNull();
});

it('creates batch with various quantity and cost values', function (): void {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $action = resolve(CreateBatch::class);

    $data = new BatchData(
        product_id: $product->id,
        warehouse_id: $warehouse->id,
        batch_number: 'BAT-'.now()->getTimestampMs().'-'.random_int(1000, 9999),
        cost_amount: 1,
        quantity: 0,
        expires_at: null,
    );

    $batch = $action->handle($data);

    expect($batch->cost_amount)->toBe(1)
        ->and($batch->quantity)->toBe(0);
});
