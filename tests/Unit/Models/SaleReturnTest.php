<?php

declare(strict_types=1);

use App\Enums\PaymentTypeEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);

    $saleReturn = SaleReturn::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ])->refresh();

    expect(array_keys($saleReturn->toArray()))
        ->toBe([
            'id',
            'reference',
            'subtotal',
            'discount',
            'tax',
            'total',
            'refunded',
            'status',
            'reason',
            'notes',
            'sale_id',
            'client_id',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('sale return relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $client = Client::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create(['created_by' => $user->id, 'store_id' => $store->id, 'client_id' => $client->id]);

    $saleReturn = SaleReturn::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'client_id' => $client->id,
        'sale_id' => $sale->id,
    ])->refresh();
    $saleReturn->update(['updated_by' => $user->id]);

    $product = Product::factory()->create(['created_by' => $user->id]);
    $saleItem = SaleItem::factory()->create(['sale_id' => $sale->id, 'product_id' => $product->id]);
    $returnItem = SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'sale_item_id' => $saleItem->id,
    ]);

    $payment = Payment::factory()->create(['type' => PaymentTypeEnum::SALE->value, 'related_id' => $saleReturn->id, 'created_by' => $user->id]);
    $stockMovement = StockMovement::factory()->create([
        'type' => StockMovementTypeEnum::SALE_RETURN->value,
        'reference' => $saleReturn->reference,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'created_by' => $user->id,
    ]);

    expect($saleReturn->creator->id)->toBe($user->id)
        ->and($saleReturn->updater->id)->toBe($user->id)
        ->and($saleReturn->store->id)->toBe($store->id)
        ->and($saleReturn->client->id)->toBe($client->id)
        ->and($saleReturn->sale->id)->toBe($sale->id)
        ->and($saleReturn->items->count())->toBe(1)
        ->and($saleReturn->items->first()->id)->toBe($returnItem->id)
        ->and($saleReturn->payments->count())->toBe(1)
        ->and($saleReturn->payments->first()->id)->toBe($payment->id)
        ->and($saleReturn->stockMovements->count())->toBe(1)
        ->and($saleReturn->stockMovements->first()->id)->toBe($stockMovement->id);
});
