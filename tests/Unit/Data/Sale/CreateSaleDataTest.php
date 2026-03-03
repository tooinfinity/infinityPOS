<?php

declare(strict_types=1);

use App\Data\Sale\CreateSaleData;
use App\Data\Sale\SaleItemData;
use Spatie\LaravelData\DataCollection;

it('may be created with required fields', function (): void {
    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(product_id: 1, batch_id: 1, quantity: 10, unit_price: 500, unit_cost: 300),
    ]);

    $data = new CreateSaleData(
        customer_id: 1,
        warehouse_id: 1,
        user_id: 1,
        sale_date: '2024-01-15',
        note: 'Test sale',
        items: $items,
    );

    expect($data)
        ->customer_id->toBe(1)
        ->warehouse_id->toBe(1)
        ->user_id->toBe(1)
        ->sale_date->toBe('2024-01-15')
        ->note->toBe('Test sale')
        ->items->toHaveCount(1);
});

it('may be created with null note', function (): void {
    $items = new DataCollection(SaleItemData::class, []);

    $data = new CreateSaleData(
        customer_id: 1,
        warehouse_id: 1,
        user_id: 1,
        sale_date: now(),
        note: null,
        items: $items,
    );

    expect($data->note)->toBeNull();
});

it('accepts multiple items', function (): void {
    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(product_id: 1, batch_id: 1, quantity: 10, unit_price: 500, unit_cost: 300),
        new SaleItemData(product_id: 2, batch_id: 2, quantity: 5, unit_price: 1000, unit_cost: 700),
    ]);

    $data = new CreateSaleData(
        customer_id: 1,
        warehouse_id: 1,
        user_id: 1,
        sale_date: now(),
        note: null,
        items: $items,
    );

    expect($data->items)->toHaveCount(2);
});
