<?php

declare(strict_types=1);

use App\Actions\StockTransfer\UpdateStockTransfer;
use App\Models\StockTransfer;
use App\Models\User;

it('may update transfer note', function (): void {
    $transfer = StockTransfer::factory()->pending()->create([
        'note' => 'Old note',
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $action->handle($transfer, [
        'note' => 'New note',
    ]);

    expect($transfer->fresh()->note)->toBe('New note');
});

it('may update transfer date', function (): void {
    $transfer = StockTransfer::factory()->pending()->create([
        'transfer_date' => now(),
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $action->handle($transfer, [
        'transfer_date' => now()->addWeek(),
    ]);

    expect($transfer->fresh()->transfer_date->isAfter(now()))->toBeTrue();
});

it('may update transfer user', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $newUser = User::factory()->create();

    $action = resolve(UpdateStockTransfer::class);

    $action->handle($transfer, [
        'user_id' => $newUser->id,
    ]);

    expect($transfer->fresh()->user_id)->toBe($newUser->id);
});

it('throws exception when updating non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create([
        'note' => 'Old note',
    ]);

    $action = resolve(UpdateStockTransfer::class);

    expect(fn () => $action->handle($transfer, [
        'note' => 'New note',
    ]))->toThrow(RuntimeException::class, 'Only pending transfers can be updated.');
});

it('keeps unchanged fields intact', function (): void {
    $transfer = StockTransfer::factory()->pending()->create([
        'note' => 'Original note',
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $action->handle($transfer, [
        'user_id' => User::factory()->create()->id,
    ]);

    expect($transfer->fresh()->note)->toBe('Original note');
});
