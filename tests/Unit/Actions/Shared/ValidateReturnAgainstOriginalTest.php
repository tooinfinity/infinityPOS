<?php

declare(strict_types=1);

use App\Actions\Shared\ValidateReturnAgainstOriginal;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\ItemNotFoundException;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;

it('validates sale return item against original sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();
    $saleReturnItem = SaleReturnItem::factory()->for($saleReturn)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->handle($saleReturnItem, $batch->product_id, $batch->id, 5);

    expect(true)->toBeTrue();
});

it('throws when product not in original sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();
    $saleReturnItem = SaleReturnItem::factory()->for($saleReturn)->create();

    $otherBatch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->handle($saleReturnItem, $otherBatch->product_id, $otherBatch->id, 5);
})->throws(ItemNotFoundException::class, 'Product is not part of the original order');

it('throws when batch does not match', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();
    $saleReturnItem = SaleReturnItem::factory()->for($saleReturn)->create();

    $otherBatch = Batch::factory()->forWarehouse($warehouse)->for($batch->product, 'product')->withQuantity(100)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->handle($saleReturnItem, $batch->product_id, $otherBatch->id, 5);
})->throws(ItemNotFoundException::class, 'batch does not match');

it('throws when returning more than purchased', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 1000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();
    $saleReturnItem = SaleReturnItem::factory()->for($saleReturn)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->handle($saleReturnItem, $batch->product_id, $batch->id, 10);
})->throws(InvalidOperationException::class, 'Cannot return more than originally purchased');

it('validates new sale return against original sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->validateNewReturn($saleReturn, $batch->product_id, $batch->id, 5);

    expect(true)->toBeTrue();
});

it('throws when validating new sale return with product not in original sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();

    $otherBatch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->validateNewReturn($saleReturn, $otherBatch->product_id, $otherBatch->id, 5);
})->throws(ItemNotFoundException::class, 'Product is not part of the original sale');

it('validates new purchase return against original purchase', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->validateNewReturnForPurchase($purchaseReturn, $batch->product_id, $batch->id, 5);

    expect(true)->toBeTrue();
});

it('throws when validating new purchase return with product not in original purchase', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create();

    $otherBatch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->validateNewReturnForPurchase($purchaseReturn, $otherBatch->product_id, $otherBatch->id, 5);
})->throws(ItemNotFoundException::class, 'Product is not part of the original purchase');

it('allows return quantity equal to remaining', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();

    SaleReturnItem::query()->forceCreate([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 200,
        'subtotal' => 1000,
    ]);

    $saleReturnItem = SaleReturnItem::factory()->for($saleReturn)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->handle($saleReturnItem, $batch->product_id, $batch->id, 5);

    expect(true)->toBeTrue();
});

it('handles null batch id', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();
    $saleReturnItem = SaleReturnItem::factory()->for($saleReturn)->create();

    $action = resolve(ValidateReturnAgainstOriginal::class);

    $action->handle($saleReturnItem, $product->id, null, 5);

    expect(true)->toBeTrue();
});
