<?php

declare(strict_types=1);

use App\Actions\Sales\ProcessSaleReturn;
use App\Data\Sales\ProcessSaleReturnData;
use App\Data\Sales\ProcessSaleReturnItemData;
use App\Enums\SaleReturnStatusEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\User;

it('may process a sale return', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $action = resolve(ProcessSaleReturn::class);

    $itemData = ProcessSaleReturnItemData::from([
        'product_id' => $product->id,
        'sale_item_id' => $saleItem->id,
        'quantity' => 2,
        'price' => 10000,
        'cost' => 5000,
        'discount' => 0,
        'tax_amount' => 200,
        'total' => 20200,
    ]);

    $data = ProcessSaleReturnData::from([
        'reference' => 'RET-001',
        'sale_id' => $sale->id,
        'client_id' => $sale->client_id,
        'store_id' => $sale->store_id,
        'subtotal' => 20000,
        'discount' => 0,
        'tax' => 200,
        'total' => 20200,
        'reason' => 'Defective product',
        'notes' => 'Customer returned 2 items',
        'items' => [$itemData],
        'created_by' => $user->id,
    ]);

    $saleReturn = $action->handle($data);

    expect($saleReturn)->toBeInstanceOf(SaleReturn::class)
        ->and($saleReturn->reference)->toBe('RET-001')
        ->and($saleReturn->sale_id)->toBe($sale->id)
        ->and($saleReturn->status)->toBe(SaleReturnStatusEnum::PENDING)
        ->and($saleReturn->reason)->toBe('Defective product')
        ->and($saleReturn->total)->toBe(20200)
        ->and($saleReturn->items)->toHaveCount(1)
        ->and($saleReturn->items->first()->product_id)->toBe($product->id)
        ->and($saleReturn->items->first()->quantity)->toBe(2);
});
