<?php

declare(strict_types=1);

use App\Actions\Pos\CalculateCartTotals;
use App\Enums\TaxTypeEnum;
use App\Models\Product;
use App\Models\Tax;

it('applies cart discount before percentage tax', function (): void {
    $tax = Tax::factory()->percentage(10)->active()->create([
        'tax_type' => TaxTypeEnum::PERCENTAGE->value,
        'rate' => 10,
        'is_active' => true,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => $tax->id,
        'is_active' => true,
    ]);

    $action = resolve(CalculateCartTotals::class);

    $totals = $action->handle([
        'line_1' => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 1000,
            'quantity' => 2,
        ],
    ], cartDiscount: 200);

    // subtotal 2000 - discount 200 => taxable 1800; tax 10% => 180; total 1980
    expect($totals->subtotal)->toBe(2000)
        ->and($totals->discount_total)->toBe(200)
        ->and($totals->tax_total)->toBe(180)
        ->and($totals->total)->toBe(1980);
});

it('caps cart discount at subtotal', function (): void {
    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => null,
        'is_active' => true,
    ]);

    $action = resolve(CalculateCartTotals::class);

    $totals = $action->handle([
        'line_1' => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 1000,
            'quantity' => 1,
        ],
    ], cartDiscount: 9999);

    expect($totals->subtotal)->toBe(1000)
        ->and($totals->discount_total)->toBe(1000)
        ->and($totals->total)->toBe(0);
});
