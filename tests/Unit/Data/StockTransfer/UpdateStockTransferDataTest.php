<?php

declare(strict_types=1);

use App\Data\StockTransfer\UpdateStockTransferData;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateStockTransferData(
        note: Optional::create(),
        transfer_date: Optional::create(),
        user_id: Optional::create(),
    );

    expect($data->note)->toBeInstanceOf(Optional::class);
});

it('may be created with specific values', function (): void {
    $transferDate = Illuminate\Support\Facades\Date::now();

    $data = new UpdateStockTransferData(
        note: 'Updated note',
        transfer_date: $transferDate,
        user_id: 5,
    );

    expect($data->note)->toBe('Updated note')
        ->and($data->transfer_date)->toBe($transferDate)
        ->and($data->user_id)->toBe(5);
});
