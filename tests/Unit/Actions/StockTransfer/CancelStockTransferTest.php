<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CancelStockTransfer;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\StockTransfer;

it('may cancel pending transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();

    $action = resolve(CancelStockTransfer::class);

    $result = $action->handle($transfer);

    expect($result)->toBeTrue()
        ->and($transfer->fresh()->status)->toBe(StockTransferStatusEnum::Cancelled);
});

it('does not change stock quantities when cancelled', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();

    $action = resolve(CancelStockTransfer::class);
    $action->handle($transfer);

    expect($transfer->fresh()->status)->toBe(StockTransferStatusEnum::Cancelled);
});

it('throws StateTransitionException when cancelling non-pending transfer', function (): void {
    $transfer = StockTransfer::factory()->completed()->create();

    $action = resolve(CancelStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(StateTransitionException::class);
});

it('throws StateTransitionException when cancelling already cancelled transfer', function (): void {
    $transfer = StockTransfer::factory()->create([
        'status' => StockTransferStatusEnum::Cancelled,
    ]);

    $action = resolve(CancelStockTransfer::class);

    expect(fn () => $action->handle($transfer))
        ->toThrow(StateTransitionException::class);
});
