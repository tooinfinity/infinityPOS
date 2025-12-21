<?php

declare(strict_types=1);

use App\Actions\Sales\CalculateSaleTotals;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

it('may calculate sale totals from items', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
        'created_by' => $user->id,
    ]);

    // Item 1: quantity 5, price 10000, discount 500, tax 500
    // Item total: (5 * 10000) - 500 = 49500, with tax = 50000
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'quantity' => 5,
        'price' => 10000,
        'discount' => 500,
        'tax_amount' => 500,
        'total' => 50000,
    ]);

    // Item 2: quantity 2, price 5000, discount 0, tax 100
    // Item total: (2 * 5000) - 0 = 10000, with tax = 10100
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'quantity' => 2,
        'price' => 5000,
        'discount' => 0,
        'tax_amount' => 100,
        'total' => 10100,
    ]);

    $action = resolve(CalculateSaleTotals::class);

    $updatedSale = $action->handle($sale);

    expect($updatedSale->subtotal)->toBe(59500) // (49500 + 10000)
        ->and($updatedSale->tax)->toBe(600) // (500 + 100)
        ->and($updatedSale->total)->toBe(60100); // (50000 + 10100)
});
