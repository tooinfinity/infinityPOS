<?php

declare(strict_types=1);

use App\Actions\Shared\ValidateStatusIsPending;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;

it('does not throw when sale status is pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create(['status' => SaleStatusEnum::Pending]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($sale);

    expect(true)->toBeTrue();
});

it('throws when sale status is not pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create(['status' => SaleStatusEnum::Completed]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($sale);
})->throws(StateTransitionException::class);

it('does not throw when sale return status is pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $saleReturn = SaleReturn::factory()->forSale($sale)->create(['status' => ReturnStatusEnum::Pending]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($saleReturn);

    expect(true)->toBeTrue();
});

it('throws when sale return status is not pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $saleReturn = SaleReturn::factory()->forSale($sale)->create(['status' => ReturnStatusEnum::Completed]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($saleReturn);
})->throws(StateTransitionException::class);

it('does not throw when purchase status is pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create(['status' => PurchaseStatusEnum::Pending]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($purchase);

    expect(true)->toBeTrue();
});

it('throws when purchase status is not pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create(['status' => PurchaseStatusEnum::Received]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($purchase);
})->throws(StateTransitionException::class);

it('does not throw when purchase return status is pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->create(['status' => ReturnStatusEnum::Pending]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($purchaseReturn);

    expect(true)->toBeTrue();
});

it('throws when purchase return status is not pending', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->create(['status' => ReturnStatusEnum::Completed]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($purchaseReturn);
})->throws(StateTransitionException::class);

it('does not throw when stock transfer status is pending', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $stockTransfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->create(['status' => StockTransferStatusEnum::Pending]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($stockTransfer);

    expect(true)->toBeTrue();
});

it('throws when stock transfer status is not pending', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $stockTransfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->create(['status' => StockTransferStatusEnum::Completed]);

    $action = resolve(ValidateStatusIsPending::class);

    $action->handle($stockTransfer);
})->throws(StateTransitionException::class);

it('does not throw when validating stock transfer item with pending transfer', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($fromWarehouse)->withQuantity(100)->create();

    $stockTransfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->create(['status' => StockTransferStatusEnum::Pending]);

    $stockTransferItem = StockTransferItem::factory()->for($stockTransfer)->for($batch)->create();

    $action = resolve(ValidateStatusIsPending::class);

    $action->forItem($stockTransferItem);

    expect(true)->toBeTrue();
});

it('throws when validating stock transfer item with non-pending transfer', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($fromWarehouse)->withQuantity(100)->create();

    $stockTransfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->create(['status' => StockTransferStatusEnum::Completed]);

    $stockTransferItem = StockTransferItem::factory()->for($stockTransfer)->for($batch)->create();

    $action = resolve(ValidateStatusIsPending::class);

    $action->forItem($stockTransferItem);
})->throws(StateTransitionException::class);
