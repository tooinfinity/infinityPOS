<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);

    $sale = Sale::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ])->refresh();

    expect(array_keys($sale->toArray()))
        ->toBe([
            'id',
            'reference',
            'subtotal',
            'discount',
            'tax',
            'total',
            'paid',
            'status',
            'notes',
            'client_id',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('sale relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $client = Client::factory()->create(['created_by' => $user->id]);

    $sale = Sale::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'client_id' => $client->id,
    ])->refresh();
    $sale->update(['updated_by' => $user->id]);

    $product = Product::factory()->create(['created_by' => $user->id]);
    $item = SaleItem::factory()->create(['sale_id' => $sale->id, 'product_id' => $product->id]);
    $return = SaleReturn::factory()->create(['sale_id' => $sale->id, 'store_id' => $store->id, 'created_by' => $user->id]);
    $invoice = Invoice::factory()->create(['sale_id' => $sale->id, 'created_by' => $user->id]);
    $payment = Payment::factory()->forSale($sale->id)->create(['created_by' => $user->id]);
    $stockMovement = StockMovement::factory()->create([
        'source_type' => Sale::class,
        'source_id' => $sale->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'created_by' => $user->id,
    ]);

    expect($sale->creator->id)->toBe($user->id)
        ->and($sale->updater->id)->toBe($user->id)
        ->and($sale->store->id)->toBe($store->id)
        ->and($sale->client->id)->toBe($client->id)
        ->and($sale->items->count())->toBe(1)
        ->and($sale->items->first()->id)->toBe($item->id)
        ->and($sale->returns->count())->toBe(1)
        ->and($sale->returns->first()->id)->toBe($return->id)
        ->and($sale->invoice?->id)->toBe($invoice->id)
        ->and($sale->payments->count())->toBe(1)
        ->and($sale->payments->first()->id)->toBe($payment->id)
        ->and($sale->stockMovements->count())->toBe(1)
        ->and($sale->stockMovements->first()->id)->toBe($stockMovement->id);
});
