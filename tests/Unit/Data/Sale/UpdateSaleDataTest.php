<?php

declare(strict_types=1);

use App\Data\Sale\UpdateSaleData;
use Carbon\CarbonInterface;

it('may be created with all nullable fields', function (): void {
    $data = new UpdateSaleData(
        customer_id: 1,
        sale_date: Illuminate\Support\Facades\Date::parse('2024-01-20'),
        note: 'Updated note',
    );

    expect($data)
        ->customer_id->toBe(1)
        ->sale_date->toBeInstanceOf(CarbonInterface::class)
        ->note->toBe('Updated note');
});

it('may be created with null values', function (): void {
    $data = new UpdateSaleData(
        customer_id: null,
        sale_date: null,
        note: null,
    );

    expect($data->customer_id)->toBeNull()
        ->and($data->sale_date)->toBeNull()
        ->and($data->note)->toBeNull();
});

it('may be created with partial values', function (): void {
    $data = new UpdateSaleData(
        customer_id: 5,
        sale_date: null,
        note: 'Partial update',
    );

    expect($data->customer_id)->toBe(5)
        ->and($data->sale_date)->toBeNull()
        ->and($data->note)->toBe('Partial update');
});
