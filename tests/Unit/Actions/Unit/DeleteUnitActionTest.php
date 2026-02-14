<?php

declare(strict_types=1);

use App\Actions\Unit\DeleteUnitAction;
use App\Models\Product;
use App\Models\Unit;

beforeEach(function (): void {
    Unit::factory()->create([
        'name' => 'Piece',
        'short_name' => 'pc',
        'is_active' => true,
    ]);
});

it('may delete a unit', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Test Unit '.uniqid(),
        'short_name' => 'tu',
    ]);

    $action = resolve(DeleteUnitAction::class);

    $result = $action->handle($unit);

    expect($result)->toBeTrue()
        ->and($unit->exists)->toBeFalse();
});

it('reassigns products to default unit when deleting', function (): void {
    $defaultUnit = Unit::query()
        ->where('name', 'Piece')
        ->where('short_name', 'pc')
        ->first();

    $unitToDelete = Unit::factory()->create([
        'name' => 'Unit To Delete '.uniqid(),
        'short_name' => 'utd',
    ]);
    $product = Product::factory()->create([
        'unit_id' => $unitToDelete->id,
    ]);

    expect($product->unit_id)->toBe($unitToDelete->id);

    $action = resolve(DeleteUnitAction::class);
    $action->handle($unitToDelete);

    expect($product->refresh()->unit_id)->toBe($defaultUnit->id);
});

it('reassigns multiple products to default unit when deleting', function (): void {
    $defaultUnit = Unit::query()
        ->where('name', 'Piece')
        ->where('short_name', 'pc')
        ->first();

    $unitToDelete = Unit::factory()->create([
        'name' => 'Unit To Delete '.uniqid(),
        'short_name' => 'utd',
    ]);
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
    $unit = Unit::factory()->create([
        'name' => 'Test Unit '.uniqid(),
        'short_name' => 'tu',
    ]);

    $action = resolve(DeleteUnitAction::class);

    $result = $action->handle($unit);

    expect($result)->toBeTrue()
        ->and(Unit::query()->find($unit->id))->toBeNull();
});

it('uses provided default unit for reassignment', function (): void {
    $customDefault = Unit::factory()->create([
        'name' => 'Custom Default '.uniqid(),
        'short_name' => 'cd',
        'is_active' => true,
    ]);

    $unitToDelete = Unit::factory()->create([
        'name' => 'Unit To Delete '.uniqid(),
        'short_name' => 'utd',
    ]);
    $product = Product::factory()->create([
        'unit_id' => $unitToDelete->id,
    ]);

    $action = resolve(DeleteUnitAction::class);
    $action->handle($unitToDelete, $customDefault);

    expect($product->refresh()->unit_id)->toBe($customDefault->id);
});

it('throws exception when deleting unit with products and no fallback unit', function (): void {
    Unit::query()
        ->where('name', 'Piece')
        ->where('short_name', 'pc')
        ->delete();

    $unitToDelete = Unit::factory()->create([
        'name' => 'Test Unit '.uniqid(),
        'short_name' => 'tu',
    ]);
    $product = Product::factory()->create([
        'unit_id' => $unitToDelete->id,
    ]);

    $action = resolve(DeleteUnitAction::class);

    expect(fn () => $action->handle($unitToDelete))
        ->toThrow(DomainException::class, 'Cannot delete unit with associated products without a fallback unit.')
        ->and($unitToDelete->fresh())->not->toBeNull()
        ->and($product->fresh()->unit_id)->toBe($unitToDelete->id);

});
