<?php

declare(strict_types=1);

use App\Data\Sale\SaleItemData;

it('may be created with required fields', function (): void {
    $data = new SaleItemData(
        product_id: 1,
        batch_id: 2,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    );

    expect($data)
        ->product_id->toBe(1)
        ->batch_id->toBe(2)
        ->quantity->toBe(10)
        ->unit_price->toBe(500)
        ->unit_cost->toBe(300);
});

it('calculates correct subtotal', function (): void {
    $data = new SaleItemData(
        product_id: 1,
        batch_id: 1,
        quantity: 15,
        unit_price: 250,
        unit_cost: 150,
    );

    expect($data->quantity * $data->unit_price)->toBe(3750);
});

it('handles unit cost correctly', function (): void {
    $data = new SaleItemData(
        product_id: 1,
        batch_id: 1,
        quantity: 5,
        unit_price: 1000,
        unit_cost: 800,
    );

    expect($data->unit_cost)->toBe(800)
        ->and($data->unit_price)->toBe(1000);
});
