<?php

declare(strict_types=1);

use App\Actions\SaleReturn\CompleteSaleReturn;
use App\Data\SaleReturn\CompleteSaleReturnData;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;

it('completes a pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    SaleReturnItem::factory()->forSaleReturn($saleReturn)->create();

    $action = resolve(CompleteSaleReturn::class);

    $result = $action->handle($saleReturn, new CompleteSaleReturnData(
        note: 'Completed return',
    ));

    expect($result->status)->toBe(ReturnStatusEnum::Completed)
        ->and($result->note)->toBe('Completed return');
});

it('adds stock to batches when completing return', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $saleReturn = SaleReturn::factory()->pending()->create();
    SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteSaleReturn::class);

    $action->handle($saleReturn, new CompleteSaleReturnData());

    expect($batch->fresh()->quantity)->toBe(110);
});

it('throws exception when completing non-pending return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();
    SaleReturnItem::factory()->forSaleReturn($saleReturn)->create();

    $action = resolve(CompleteSaleReturn::class);

    $action->handle($saleReturn, new CompleteSaleReturnData());
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "Completed"');

it('throws exception when completing return with no items', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();

    $action = resolve(CompleteSaleReturn::class);

    $action->handle($saleReturn, new CompleteSaleReturnData());
})->throws(InvalidOperationException::class, 'Cannot complete SaleReturn. Sale return cannot be completed without items');

it('skips items without batch when completing return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'batch_id' => null,
        'quantity' => 10,
    ]);

    $action = resolve(CompleteSaleReturn::class);

    $result = $action->handle($saleReturn, new CompleteSaleReturnData());

    expect($result->status)->toBe(ReturnStatusEnum::Completed);
});
