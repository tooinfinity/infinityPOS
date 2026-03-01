<?php

declare(strict_types=1);

use App\Actions\Sale\CompleteSale;
use App\Data\Sale\CompleteSaleData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;

it('completes a pending sale', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);

    $action->handle($sale);

    expect($sale->fresh()->status)->toBe(SaleStatusEnum::Completed);
});

it('deducts batch quantity on completion', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 25,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale);

    expect($batch->fresh()->quantity)->toBe(75);
});

it('creates stock movements on completion', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 20,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale);

    $movements = StockMovement::query()
        ->where('reference_type', Sale::class)
        ->where('reference_id', $sale->id)
        ->get();

    expect($movements)->toHaveCount(1);
});

it('updates payment status to paid when fully paid', function (): void {
    $sale = Sale::factory()->pending()->paid()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale);

    expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('updates payment status to partial when partially paid', function (): void {
    $sale = Sale::factory()->pending()->partiallyPaid()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 1000,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale);

    expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial);
});

it('updates payment status to unpaid when no payment', function (): void {
    $sale = Sale::factory()->pending()->unpaid()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale);

    expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('throws exception when sale is not pending', function (): void {
    $sale = Sale::factory()->completed()->create();

    $action = resolve(CompleteSale::class);
    $action->handle($sale);
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "Completed"');

it('throws exception when sale has no items', function (): void {
    $sale = Sale::factory()->pending()->create();

    $action = resolve(CompleteSale::class);
    $action->handle($sale);
})->throws(InvalidOperationException::class, 'Cannot complete Sale. Sale cannot be completed without items');

it('can update note when completing', function (): void {
    $sale = Sale::factory()->pending()->create([
        'note' => 'Original note',
    ]);
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale, new CompleteSaleData(note: 'Updated note during completion'));

    expect($sale->fresh()->note)->toBe('Updated note during completion');
});

it('skips stock deduction for items without batch', function (): void {
    $sale = Sale::factory()->pending()->create();
    $product = App\Models\Product::factory()->create();
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale);

    expect($sale->fresh()->status)->toBe(SaleStatusEnum::Completed);
});

it('handles mixed items with and without batch', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    $productWithoutBatch = App\Models\Product::factory()->create();

    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $productWithoutBatch->id,
        'batch_id' => null,
        'quantity' => 5,
        'unit_price' => 200,
        'unit_cost' => 100,
    ]);

    $action = resolve(CompleteSale::class);
    $action->handle($sale);

    expect($sale->fresh()->status)->toBe(SaleStatusEnum::Completed);
    expect($batch->fresh()->quantity)->toBe(90);
});
