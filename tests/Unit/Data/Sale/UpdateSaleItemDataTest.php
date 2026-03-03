<?php

declare(strict_types=1);

use App\Data\Sale\UpdateSaleItemData;

it('may be created with all nullable fields', function (): void {
    $data = new UpdateSaleItemData(
        batch_id: 1,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    );

    expect($data)
        ->batch_id->toBe(1)
        ->quantity->toBe(10)
        ->unit_price->toBe(500)
        ->unit_cost->toBe(300);
});

it('may be created with null values', function (): void {
    $data = new UpdateSaleItemData(
        batch_id: null,
        quantity: null,
        unit_price: null,
        unit_cost: null,
    );

    expect($data->batch_id)->toBeNull()
        ->and($data->quantity)->toBeNull()
        ->and($data->unit_price)->toBeNull()
        ->and($data->unit_cost)->toBeNull();
});

it('may be created with partial values', function (): void {
    $data = new UpdateSaleItemData(
        batch_id: null,
        quantity: 20,
        unit_price: null,
        unit_cost: null,
    );

    expect($data->batch_id)->toBeNull()
        ->and($data->quantity)->toBe(20)
        ->and($data->unit_price)->toBeNull()
        ->and($data->unit_cost)->toBeNull();
});
