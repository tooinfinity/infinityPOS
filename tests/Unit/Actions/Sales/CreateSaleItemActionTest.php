<?php

declare(strict_types=1);

use App\Actions\Sales\CreateSaleItem;
use App\Data\Sales\CreateSaleItemData;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

it('may create a sale item', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateSaleItem::class);

    $data = CreateSaleItemData::from([
        'product_id' => $product->id,
        'quantity' => 5,
        'price' => 10000,
        'cost' => 5000,
        'discount' => 500,
        'tax_amount' => 500,
        'total' => 50000,
        'batch_number' => 'BATCH-001',
        'expiry_date' => now()->addYear()->toDateString(),
    ]);

    $saleItem = $action->handle($sale, $data);

    expect($saleItem)->toBeInstanceOf(SaleItem::class)
        ->and($saleItem->sale_id)->toBe($sale->id)
        ->and($saleItem->product_id)->toBe($product->id)
        ->and($saleItem->quantity)->toBe(5)
        ->and($saleItem->price)->toBe(10000)
        ->and($saleItem->cost)->toBe(5000)
        ->and($saleItem->discount)->toBe(500)
        ->and($saleItem->tax_amount)->toBe(500)
        ->and($saleItem->total)->toBe(50000)
        ->and($saleItem->batch_number)->toBe('BATCH-001');
});
