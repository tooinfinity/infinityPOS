<?php

declare(strict_types=1);

use App\Actions\Warehouse\DeleteWarehouse;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;

it('may delete a warehouse with no related records', function (): void {
    $warehouse = Warehouse::factory()->create();

    $action = resolve(DeleteWarehouse::class);

    $result = $action->handle($warehouse);

    expect($result)->toBeTrue()
        ->and($warehouse->exists)->toBeFalse();
});

it('throws exception when warehouse has batches', function (): void {
    $warehouse = Warehouse::factory()->create();
    Batch::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing batches');
});

it('throws exception when warehouse has purchases', function (): void {
    $warehouse = Warehouse::factory()->create();
    Purchase::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing purchases');
});

it('throws exception when warehouse has sales', function (): void {
    $warehouse = Warehouse::factory()->create();
    Sale::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing sales');
});

it('throws exception when warehouse has stock movements', function (): void {
    $warehouse = Warehouse::factory()->create();
    StockMovement::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing stockMovements');
});

it('throws exception when warehouse has transfers from', function (): void {
    $warehouse = Warehouse::factory()->create();
    StockTransfer::factory()->fromWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing transfersFrom');
});

it('throws exception when warehouse has transfers to', function (): void {
    $warehouse = Warehouse::factory()->create();
    StockTransfer::factory()->toWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing transfersTo');
});

it('throws exception when warehouse has sale returns', function (): void {
    $warehouse = Warehouse::factory()->create();
    SaleReturn::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing saleReturns');
});

it('throws exception when warehouse has purchase returns', function (): void {
    $warehouse = Warehouse::factory()->create();
    PurchaseReturn::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class, 'Cannot delete warehouse with existing purchaseReturns');
});

it('includes all related record types in exception message', function (): void {
    $warehouse = Warehouse::factory()->create();
    Batch::factory()->forWarehouse($warehouse)->create();
    Sale::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(function (RuntimeException $e): void {
            expect($e->getMessage())->toContain('batches')
                ->and($e->getMessage())->toContain('sales');
        });
});

it('does not delete warehouse when any related record exists', function (): void {
    $warehouse = Warehouse::factory()->create();
    Batch::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    try {
        $action->handle($warehouse);
        test()->fail('Expected RuntimeException to be thrown');
    } catch (RuntimeException) {
        // Expected exception
    }

    expect(Warehouse::query()->find($warehouse->id))->not->toBeNull();
});

it('rolls back transaction when exception is thrown', function (): void {
    $warehouse = Warehouse::factory()->create();
    Batch::factory()->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    try {
        $action->handle($warehouse);
        test()->fail('Expected RuntimeException to be thrown');
    } catch (RuntimeException) {
        // Expected exception
    }

    expect(Warehouse::query()->find($warehouse->id))->not->toBeNull();
});

it('prevents deletion when multiple related records exist', function (): void {
    $warehouse = Warehouse::factory()->create();
    Batch::factory()->count(3)->forWarehouse($warehouse)->create();
    Sale::factory()->count(2)->forWarehouse($warehouse)->create();

    $action = resolve(DeleteWarehouse::class);

    expect(fn () => $action->handle($warehouse))
        ->toThrow(RuntimeException::class);
});
