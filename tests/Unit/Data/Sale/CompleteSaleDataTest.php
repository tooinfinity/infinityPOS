<?php

declare(strict_types=1);

use App\Data\Sale\CompleteSaleData;

it('may be created with null note', function (): void {
    $data = new CompleteSaleData(
        note: null,
    );

    expect($data->note)->toBeNull();
});

it('may be created with note', function (): void {
    $data = new CompleteSaleData(
        note: 'Sale completed',
    );

    expect($data->note)->toBe('Sale completed');
});
