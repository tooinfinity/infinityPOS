<?php

declare(strict_types=1);

use App\Actions\Unit\DeleteUnitAction;
use App\Models\Product;
use App\Models\Unit;

it('may delete a unit', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(DeleteUnitAction::class);

    $result = $action->handle($unit);

    expect($result)->toBeTrue()
        ->and($unit->exists)->toBeFalse();
});

it('reassigns products to default unit when deleting', function (): void {
    $defaultUnit = Unit::factory()->create([
        'name' => 'Piece',
        'short_name' => 'pc',
        'is_active' => true,
    ]);

    $unitToDelete = Unit::factory()->create();
    $product = Product::factory()->create([
        'unit_id' => $unitToDelete->id,
    ]);

    expect($product->unit_id)->toBe($unitToDelete->id);

    $action = resolve(DeleteUnitAction::class);
    $action->handle($unitToDelete);

    expect($product->refresh()->unit_id)->toBe($defaultUnit->id);
});

it('reassigns multiple products to default unit when deleting', function (): void {
    $defaultUnit = Unit::factory()->create([
        'name' => 'Piece',
        'short_name' => 'pc',
        'is_active' => true,
    ]);

    $unitToDelete = Unit::factory()->create();
    $products = Product::factory()->count(3)->create([
        'unit_id' => $unitToDelete->id,
    ]);

    $action = resolve(DeleteUnitAction::class);
    $action->handle($unitToDelete);

    foreach ($products as $product) {
        expect($product->refresh()->unit_id)->toBe($defaultUnit->id);
    }
});

it('deletes unit without products', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(DeleteUnitAction::class);

    $result = $action->handle($unit);

    expect($result)->toBeTrue()
        ->and(Unit::query()->find($unit->id))->toBeNull();
});

it('uses provided default unit for reassignment', function (): void {
    $customDefault = Unit::factory()->create([
        'name' => 'Kilogram',
        'short_name' => 'kg',
        'is_active' => true,
    ]);

    $unitToDelete = Unit::factory()->create();
    $product = Product::factory()->create([
        'unit_id' => $unitToDelete->id,
    ]);

    $action = resolve(DeleteUnitAction::class);
    $action->handle($unitToDelete, $customDefault);

    expect($product->refresh()->unit_id)->toBe($customDefault->id);
});
