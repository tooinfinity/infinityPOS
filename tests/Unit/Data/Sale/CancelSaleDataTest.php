<?php

declare(strict_types=1);

use App\Data\Sale\CancelSaleData;

it('may be created with default restock_items', function (): void {
    $data = new CancelSaleData(
        restock_items: false,
        note: null,
    );

    expect($data->restock_items)->toBeFalse()
        ->and($data->note)->toBeNull();
});

it('may be created with restock_items true', function (): void {
    $data = new CancelSaleData(
        restock_items: true,
        note: 'Restocking items',
    );

    expect($data->restock_items)->toBeTrue()
        ->and($data->note)->toBe('Restocking items');
});

it('default restock_items is false', function (): void {
    $data = new CancelSaleData(
        restock_items: false,
        note: null,
    );

    expect($data->restock_items)->toBeFalse();
});
