<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CancelStockTransfer;
use App\Enums\StockTransferStatusEnum;
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
