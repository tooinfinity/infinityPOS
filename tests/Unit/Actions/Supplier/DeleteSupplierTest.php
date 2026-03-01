<?php

declare(strict_types=1);

use App\Actions\Supplier\DeleteSupplier;
use App\Exceptions\InvalidOperationException;
use App\Models\Purchase;
use App\Models\Supplier;

it('may delete a supplier', function (): void {
    $supplier = Supplier::factory()->create();

    $action = resolve(DeleteSupplier::class);

    $result = $action->handle($supplier);

    expect($result)->toBeTrue()
        ->and($supplier->exists)->toBeFalse();
});

it('throws exception when deleting supplier with purchases', function (): void {
    $supplier = Supplier::factory()->create();
    Purchase::factory()->create([
        'supplier_id' => $supplier->id,
    ]);

    $action = resolve(DeleteSupplier::class);

    expect(fn () => $action->handle($supplier))
        ->toThrow(InvalidOperationException::class, 'Cannot delete Supplier. Cannot delete supplier with associated purchases.');
});

it('throws exception when deleting supplier with multiple purchases', function (): void {
    $supplier = Supplier::factory()->create();
    Purchase::factory()->count(3)->create([
        'supplier_id' => $supplier->id,
    ]);

    $action = resolve(DeleteSupplier::class);

    expect(fn () => $action->handle($supplier))
        ->toThrow(InvalidOperationException::class, 'Cannot delete Supplier. Cannot delete supplier with associated purchases.');
});

it('deletes supplier without purchases', function (): void {
    $supplier = Supplier::factory()->create();

    $action = resolve(DeleteSupplier::class);

    $result = $action->handle($supplier);

    expect($result)->toBeTrue()
        ->and(Supplier::query()->find($supplier->id))->toBeNull();
});

it('removes supplier from database', function (): void {
    $supplier = Supplier::factory()->create();

    $action = resolve(DeleteSupplier::class);
    $action->handle($supplier);

    $this->assertDatabaseMissing('suppliers', [
        'id' => $supplier->id,
    ]);
});

it('does not delete supplier when purchases exist', function (): void {
    $supplier = Supplier::factory()->create();
    Purchase::factory()->create([
        'supplier_id' => $supplier->id,
    ]);

    $action = resolve(DeleteSupplier::class);

    try {
        $action->handle($supplier);
    } catch (InvalidOperationException) {
        // Expected exception
    }

    expect(Supplier::query()->find($supplier->id))->not->toBeNull();
});
