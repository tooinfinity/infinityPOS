<?php

declare(strict_types=1);

use App\Data\StockTransfer\UpdateStockTransferItemData;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateStockTransferItemData(
        batch_id: Optional::create(),
        quantity: Optional::create(),
    );

    expect($data->batch_id)->toBeInstanceOf(Optional::class);
});

it('may be created with specific values', function (): void {
    $data = new UpdateStockTransferItemData(
        batch_id: 10,
        quantity: 100,
    );

    expect($data->batch_id)->toBe(10)
        ->and($data->quantity)->toBe(100);
});

it('may be created with null batch_id', function (): void {
    $data = new UpdateStockTransferItemData(
        batch_id: null,
        quantity: Optional::create(),
    );

    expect($data->batch_id)->toBeNull();
});
