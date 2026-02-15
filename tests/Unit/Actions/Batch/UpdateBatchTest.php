<?php

declare(strict_types=1);

use App\Actions\Batch\UpdateBatch;
use App\Models\Batch;

it('may update a batch batch_number', function (): void {
    $batch = Batch::factory()->create([
        'batch_number' => 'OLD-BATCH',
    ]);

    $action = resolve(UpdateBatch::class);

    $action->handle($batch, [
        'batch_number' => 'NEW-BATCH',
    ]);

    expect($batch->fresh()->batch_number)->toBe('NEW-BATCH');
});

it('may update a batch cost_amount', function (): void {
    $batch = Batch::factory()->create([
        'cost_amount' => 5000,
    ]);

    $action = resolve(UpdateBatch::class);

    $action->handle($batch, [
        'cost_amount' => 7500,
    ]);

    expect($batch->fresh()->cost_amount)->toBe(7500);
});

it('may update a batch quantity', function (): void {
    $batch = Batch::factory()->create([
        'quantity' => 100,
    ]);

    $action = resolve(UpdateBatch::class);

    $action->handle($batch, [
        'quantity' => 200,
    ]);

    expect($batch->fresh()->quantity)->toBe(200);
});

it('updates batch expires_at', function (): void {
    $batch = Batch::factory()->create([
        'expires_at' => now()->addMonths(6),
    ]);

    $action = resolve(UpdateBatch::class);

    $action->handle($batch, [
        'expires_at' => now()->addYear(),
    ]);

    expect($batch->fresh()->expires_at->isAfter(now()->addMonths(11)))->toBeTrue();
});

it('updates multiple fields at once', function (): void {
    $batch = Batch::factory()->create([
        'batch_number' => 'OLD-BATCH',
        'cost_amount' => 5000,
        'quantity' => 100,
    ]);

    $action = resolve(UpdateBatch::class);

    $action->handle($batch, [
        'batch_number' => 'NEW-BATCH',
        'cost_amount' => 10000,
        'quantity' => 500,
    ]);

    $fresh = $batch->fresh();
    expect($fresh->batch_number)->toBe('NEW-BATCH')
        ->and($fresh->cost_amount)->toBe(10000)
        ->and($fresh->quantity)->toBe(500);
});

it('sets nullable fields to null', function (): void {
    $batch = Batch::factory()->create([
        'batch_number' => 'BATCH-001',
        'expires_at' => now()->addYear(),
    ]);

    $action = resolve(UpdateBatch::class);

    $action->handle($batch, [
        'batch_number' => null,
        'expires_at' => null,
    ]);

    $fresh = $batch->fresh();
    expect($fresh->batch_number)->toBeNull()
        ->and($fresh->expires_at)->toBeNull();
});

it('keeps unchanged fields intact', function (): void {
    $batch = Batch::factory()->create([
        'batch_number' => 'BATCH-001',
        'cost_amount' => 5000,
        'quantity' => 100,
    ]);

    $action = resolve(UpdateBatch::class);

    $action->handle($batch, [
        'quantity' => 200,
    ]);

    $fresh = $batch->fresh();
    expect($fresh->batch_number)->toBe('BATCH-001')
        ->and($fresh->cost_amount)->toBe(5000)
        ->and($fresh->quantity)->toBe(200);
});
