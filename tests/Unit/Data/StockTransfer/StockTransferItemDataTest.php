<?php

declare(strict_types=1);

use App\Data\StockTransfer\StockTransferItemData;

it('may be created with required fields', function (): void {
    $data = new StockTransferItemData(
        product_id: 1,
        batch_id: null,
        quantity: 50,
    );

    expect($data)
        ->product_id->toBe(1)
        ->batch_id->toBeNull()
        ->quantity->toBe(50);
});

it('may be created with batch', function (): void {
    $data = new StockTransferItemData(
        product_id: 1,
        batch_id: 10,
        quantity: 50,
    );

    expect($data->batch_id)->toBe(10);
});
