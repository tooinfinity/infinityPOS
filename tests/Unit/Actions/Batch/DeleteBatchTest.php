<?php

declare(strict_types=1);

use App\Actions\Batch\DeleteBatch;
use App\Models\Batch;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\StockTransferItem;

it('may delete a batch with no related records', function (): void {
    $batch = Batch::factory()->create();

    $action = resolve(DeleteBatch::class);

    $result = $action->handle($batch);

    expect($result)->toBeTrue()
        ->and($batch->exists)->toBeFalse();
});

it('throws exception when batch has stock movements', function (): void {
    $batch = Batch::factory()->create();
    StockMovement::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    expect(fn () => $action->handle($batch))
        ->toThrow(RuntimeException::class, 'Cannot delete batch with existing stockMovements');
});

it('throws exception when batch has purchase items', function (): void {
    $batch = Batch::factory()->create();
    PurchaseItem::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    expect(fn () => $action->handle($batch))
        ->toThrow(RuntimeException::class, 'Cannot delete batch with existing purchaseItems');
});

it('throws exception when batch has sale items', function (): void {
    $batch = Batch::factory()->create();
    SaleItem::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    expect(fn () => $action->handle($batch))
        ->toThrow(RuntimeException::class, 'Cannot delete batch with existing saleItems');
});

it('throws exception when batch has stock transfer items', function (): void {
    $batch = Batch::factory()->create();
    StockTransferItem::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    expect(fn () => $action->handle($batch))
        ->toThrow(RuntimeException::class, 'Cannot delete batch with existing stockTransferItems');
});

it('throws exception when batch has sale return items', function (): void {
    $batch = Batch::factory()->create();
    SaleReturnItem::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    expect(fn () => $action->handle($batch))
        ->toThrow(RuntimeException::class, 'Cannot delete batch with existing saleReturnItems');
});

it('throws exception when batch has purchase return items', function (): void {
    $batch = Batch::factory()->create();
    PurchaseReturnItem::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    expect(fn () => $action->handle($batch))
        ->toThrow(RuntimeException::class, 'Cannot delete batch with existing purchaseReturnItems');
});

it('includes all related record types in exception message', function (): void {
    $batch = Batch::factory()->create();
    StockMovement::factory()->forBatch($batch)->create();
    SaleItem::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    expect(fn () => $action->handle($batch))
        ->toThrow(function (RuntimeException $e): void {
            expect($e->getMessage())->toContain('stockMovements')
                ->and($e->getMessage())->toContain('saleItems');
        });
});

it('does not delete batch when any related record exists', function (): void {
    $batch = Batch::factory()->create();
    StockMovement::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    try {
        $action->handle($batch);
        test()->fail('Expected RuntimeException to be thrown');
    } catch (RuntimeException) {
        // Expected exception
    }

    expect(Batch::query()->find($batch->id))->not->toBeNull();
});

it('rolls back transaction when exception is thrown', function (): void {
    $batch = Batch::factory()->create();
    StockMovement::factory()->forBatch($batch)->create();

    $action = resolve(DeleteBatch::class);

    try {
        $action->handle($batch);
        test()->fail('Expected RuntimeException to be thrown');
    } catch (RuntimeException) {
        // Expected exception
    }

    expect(Batch::query()->find($batch->id))->not->toBeNull();
});
