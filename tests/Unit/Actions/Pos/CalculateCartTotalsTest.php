<?php

declare(strict_types=1);

use App\Actions\Pos\CalculateCartTotals;
use App\Enums\TaxTypeEnum;
use App\Models\Product;
use App\Models\Tax;

it('calculates cart totals with percentage tax', function (): void {
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
    ]);

    expect($totals->subtotal)->toBe(2000)
        ->and($totals->tax_total)->toBe(200)
        ->and($totals->total)->toBe(2200);
});

it('calculates cart totals with fixed tax per unit', function (): void {
    $tax = Tax::factory()->fixed(50)->active()->create([
        'tax_type' => TaxTypeEnum::FIXED->value,
        'rate' => 50,
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
            'quantity' => 3,
        ],
    ]);

    expect($totals->subtotal)->toBe(3000)
        ->and($totals->tax_total)->toBe(150)
        ->and($totals->total)->toBe(3150);
});

it('skips tax calculation for inactive taxes', function (): void {
    $tax = Tax::factory()->create([
        'tax_type' => TaxTypeEnum::PERCENTAGE,
        'rate' => 10,
        'is_active' => false,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => $tax->id,
    ]);

    $action = resolve(CalculateCartTotals::class);

    $totals = $action->handle([
        'line_1' => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 1000,
            'quantity' => 2,
        ],
    ]);

    expect($totals->subtotal)->toBe(2000)
        ->and($totals->tax_total)->toBe(0)
        ->and($totals->total)->toBe(2000);
});

it('handles products without tax', function (): void {
    $product = Product::factory()->create([
        'price' => 1500,
        'tax_id' => null,
    ]);

    $action = resolve(CalculateCartTotals::class);

    $totals = $action->handle([
        'line_1' => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 1500,
            'quantity' => 2,
        ],
    ]);

    expect($totals->subtotal)->toBe(3000)
        ->and($totals->tax_total)->toBe(0)
        ->and($totals->total)->toBe(3000);
});

it('recalculates tax with cart discount applied', function (): void {
    $tax = Tax::factory()->create([
        'tax_type' => TaxTypeEnum::PERCENTAGE,
        'rate' => 10,
        'is_active' => true,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => $tax->id,
    ]);

    $action = resolve(CalculateCartTotals::class);

    // With 500 discount on 2000 subtotal, tax should be on 1500
    $totals = $action->handle([
        'line_1' => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 1000,
            'quantity' => 2,
        ],
    ], 500);

    expect($totals->subtotal)->toBe(2000)
        ->and($totals->discount_total)->toBe(500)
        ->and($totals->tax_total)->toBe(150) // 10% of 1500
        ->and($totals->total)->toBe(1650); // 1500 + 150
});

it('skips inactive tax when recalculating with discount', function (): void {
    $tax = Tax::factory()->create([
        'tax_type' => TaxTypeEnum::PERCENTAGE,
        'rate' => 10,
        'is_active' => false,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => $tax->id,
    ]);

    $action = resolve(CalculateCartTotals::class);

    $totals = $action->handle([
        'line_1' => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 1000,
            'quantity' => 2,
        ],
    ], 500);

    expect($totals->subtotal)->toBe(2000)
        ->and($totals->discount_total)->toBe(500)
        ->and($totals->tax_total)->toBe(0) // Inactive tax should be skipped
        ->and($totals->total)->toBe(1500);
});

it('handles fixed tax with discount', function (): void {
    $tax = Tax::factory()->create([
        'tax_type' => TaxTypeEnum::FIXED,
        'rate' => 50,
        'is_active' => true,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => $tax->id,
    ]);

    $action = resolve(CalculateCartTotals::class);

    $totals = $action->handle([
        'line_1' => [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 1000,
            'quantity' => 2,
        ],
    ], 500);

    expect($totals->subtotal)->toBe(2000)
        ->and($totals->discount_total)->toBe(500)
        ->and($totals->tax_total)->toBe(100) // Fixed tax: 50 * 2 qty
        ->and($totals->total)->toBe(1600);
});
