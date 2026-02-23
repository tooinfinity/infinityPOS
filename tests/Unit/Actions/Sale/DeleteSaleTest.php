<?php

declare(strict_types=1);

use App\Actions\Sale\DeleteSale;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;

it('deletes pending sale', function (): void {
    $sale = Sale::factory()->pending()->create();
    $saleId = $sale->id;

    $action = resolve(DeleteSale::class);

    $action->handle($sale);

    expect(Sale::query()->find($saleId))->toBeNull();
});

it('cascades delete items', function (): void {
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
    $saleId = $sale->id;

    $action = resolve(DeleteSale::class);

    $action->handle($sale);

    expect(SaleItem::query()->where('sale_id', $saleId)->count())->toBe(0);
});

it('throws exception when sale is not pending', function (): void {
    $sale = Sale::factory()->completed()->create();

    $action = resolve(DeleteSale::class);

    $action->handle($sale);
})->throws(RuntimeException::class, 'pending sales');

it('throws exception when sale is cancelled', function (): void {
    $sale = Sale::factory()->cancelled()->create();

    $action = resolve(DeleteSale::class);

    $action->handle($sale);
})->throws(RuntimeException::class, 'pending sales');

it('deletes multiple items', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 100,
        'unit_cost' => 50,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 3,
        'unit_price' => 200,
        'unit_cost' => 100,
    ]);

    $action = resolve(DeleteSale::class);

    $action->handle($sale);

    expect(SaleItem::query()->where('sale_id', $sale->id)->count())->toBe(0)
        ->and(Sale::query()->find($sale->id))->toBeNull();
});
