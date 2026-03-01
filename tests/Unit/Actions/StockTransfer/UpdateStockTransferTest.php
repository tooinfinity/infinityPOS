<?php

declare(strict_types=1);

use App\Actions\StockTransfer\UpdateStockTransfer;
use App\Data\StockTransfer\UpdateStockTransferData;
use App\Exceptions\InvalidOperationException;
use App\Models\StockTransfer;
use App\Models\User;
use Spatie\LaravelData\Optional;

it('may update transfer note', function (): void {
    $transfer = StockTransfer::factory()->pending()->create([
        'note' => 'Old note',
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $data = new UpdateStockTransferData(
        note: 'New note',
        transfer_date: Optional::create(),
        user_id: Optional::create(),
    );

    $action->handle($transfer, $data);

    expect($transfer->fresh()->note)->toBe('New note');
});

it('may update transfer date', function (): void {
    $transfer = StockTransfer::factory()->pending()->create([
        'transfer_date' => now(),
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $data = new UpdateStockTransferData(
        note: Optional::create(),
        transfer_date: now()->addWeek(),
        user_id: Optional::create(),
    );

    $action->handle($transfer, $data);

    expect($transfer->fresh()->transfer_date->isAfter(now()))->toBeTrue();
});

it('may update transfer user', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $newUser = User::factory()->create();

    $action = resolve(UpdateStockTransfer::class);

    $data = new UpdateStockTransferData(
        note: Optional::create(),
        transfer_date: Optional::create(),
        user_id: $newUser->id,
    );

    $action->handle($transfer, $data);

    expect($transfer->fresh()->user_id)->toBe($newUser->id);
});

it('throws exception when updating non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create([
        'note' => 'Old note',
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $data = new UpdateStockTransferData(
        note: 'New note',
        transfer_date: Optional::create(),
        user_id: Optional::create(),
    );

    expect(fn () => $action->handle($transfer, $data))->toThrow(InvalidOperationException::class, 'Only pending transfers can be updated.');
});

it('keeps unchanged fields intact', function (): void {
    $transfer = StockTransfer::factory()->pending()->create([
        'note' => 'Original note',
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $data = new UpdateStockTransferData(
        note: Optional::create(),
        transfer_date: Optional::create(),
        user_id: User::factory()->create()->id,
    );

    $action->handle($transfer, $data);

    expect($transfer->fresh()->note)->toBe('Original note');
});
