<?php

declare(strict_types=1);

use App\Actions\Sales\CreateSale;
use App\Data\Sales\CreateSaleData;
use App\Data\Sales\CreateSaleItemData;
use App\Enums\SaleStatusEnum;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;

it('may create a sale', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateSale::class);

    $itemData = CreateSaleItemData::from([
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

    $data = CreateSaleData::from([
        'reference' => 'SALE-001',
        'client_id' => $client->id,
        'store_id' => $store->id,
        'subtotal' => 49500,
        'discount' => 500,
        'tax' => 500,
        'total' => 50000,
        'notes' => 'Test sale',
        'items' => [$itemData],
        'created_by' => $user->id,
    ]);

    $sale = $action->handle($data);

    expect($sale)->toBeInstanceOf(Sale::class)
        ->and($sale->reference)->toBe('SALE-001')
        ->and($sale->client_id)->toBe($client->id)
        ->and($sale->store_id)->toBe($store->id)
        ->and($sale->status)->toBe(SaleStatusEnum::PENDING)
        ->and($sale->notes)->toBe('Test sale')
        ->and($sale->created_by)->toBe($user->id)
        ->and($sale->items)->toHaveCount(1)
        ->and($sale->items->first()->product_id)->toBe($product->id)
        ->and($sale->items->first()->quantity)->toBe(5)
        ->and($sale->items->first()->price)->toBe(10000);
});
