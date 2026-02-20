<?php

declare(strict_types=1);

use App\Data\Sale\QuickSaleData;
use App\Data\Sale\SaleItemData;
use Spatie\LaravelData\DataCollection;

it('may be created with required fields', function (): void {
    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(product_id: 1, batch_id: 1, quantity: 10, unit_price: 500, unit_cost: 300),
    ]);

    $data = new QuickSaleData(
        customer_id: 1,
        warehouse_id: 1,
        user_id: 1,
        payment_method_id: 1,
        sale_date: Illuminate\Support\Facades\Date::now(),
        paid_amount: 5000,
        note: null,
        items: $items,
    );

    expect($data)
        ->customer_id->toBe(1)
        ->warehouse_id->toBe(1)
        ->user_id->toBe(1)
        ->payment_method_id->toBe(1)
        ->paid_amount->toBe(5000)
        ->items->toHaveCount(1);
});

it('may be created with note', function (): void {
    $items = new DataCollection(SaleItemData::class, []);

    $data = new QuickSaleData(
        customer_id: 1,
        warehouse_id: 1,
        user_id: 1,
        payment_method_id: 1,
        sale_date: now(),
        paid_amount: 1000,
        note: 'Quick sale',
        items: $items,
    );

    expect($data->note)->toBe('Quick sale');
});

it('accepts multiple items', function (): void {
    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(product_id: 1, batch_id: 1, quantity: 5, unit_price: 500, unit_cost: 300),
        new SaleItemData(product_id: 2, batch_id: 2, quantity: 10, unit_price: 300, unit_cost: 200),
    ]);

    $data = new QuickSaleData(
        customer_id: 1,
        warehouse_id: 1,
        user_id: 1,
        payment_method_id: 1,
        sale_date: now(),
        paid_amount: 5500,
        note: null,
        items: $items,
    );

    expect($data->items)->toHaveCount(2)
        ->and($data->paid_amount)->toBe(5500);
});
