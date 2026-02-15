<?php

declare(strict_types=1);

use App\Actions\GenerateUniqueSku;
use App\Models\Product;

it('generates unique SKU in correct format', function (): void {
    $action = resolve(GenerateUniqueSku::class);

    $sku = $action->handle();

    expect($sku)
        ->toStartWith('PRD-')
        ->toHaveLength(10)
        ->and(preg_match('/^PRD-[A-Z0-9]{6}$/', $sku))->toBe(1);
});

it('generates different SKUs on consecutive calls', function (): void {
    $action = resolve(GenerateUniqueSku::class);

    $sku1 = $action->handle();
    $sku2 = $action->handle();

    expect($sku1)->not->toBe($sku2);
});

it('ensures uniqueness when duplicate exists', function (): void {
    Product::factory()->create(['sku' => 'PRD-ABC123']);

    $action = resolve(GenerateUniqueSku::class);

    $sku = $action->handle();

    expect($sku)->not->toBe('PRD-ABC123')
        ->and(Product::query()->where('sku', $sku)->exists())->toBeFalse();
});

it('generates uppercase SKU', function (): void {
    $action = resolve(GenerateUniqueSku::class);

    $sku = $action->handle();

    expect($sku)->toBe(mb_strtoupper($sku));
});

it('generates SKU with alphanumeric characters only', function (): void {
    $action = resolve(GenerateUniqueSku::class);

    $sku = $action->handle();

    $codePart = mb_substr($sku, 4);
    expect(preg_match('/^[A-Z0-9]+$/', $codePart))->toBe(1);
});

it('continues generating until unique SKU found', function (): void {
    Product::factory()->create(['sku' => 'PRD-AAAAAA']);
    Product::factory()->create(['sku' => 'PRD-BBBBBB']);

    $action = resolve(GenerateUniqueSku::class);

    $sku = $action->handle();

    expect($sku)->not->toBeIn(['PRD-AAAAAA', 'PRD-BBBBBB'])
        ->and(Product::query()->where('sku', $sku)->exists())->toBeFalse();
});
