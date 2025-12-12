<?php

declare(strict_types=1);

use App\Data\ClientData;
use App\Data\SaleData;
use App\Data\StoreData;
use App\Data\UserData;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;

it('transforms a sale model into SaleData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $client = Client::factory()->create();
    $store = Store::factory()->create();

    /** @var Sale $sale */
    $sale = Sale::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($client, 'client')
        ->for($store, 'store')
        ->create([
            'reference' => 'SL-0001',
            'subtotal' => 10000,
            'discount' => 500,
            'tax' => 1900,
            'total' => 11400,
            'paid' => 5000,
            'status' => App\Enums\SaleStatusEnum::COMPLETED->value,
            'notes' => 'Thanks for your purchase',
        ]);

    $data = SaleData::from(
        $sale->load(['creator', 'updater', 'client', 'store'])
    );

    expect($data)
        ->toBeInstanceOf(SaleData::class)
        ->id->toBe($sale->id)
        ->reference->toBe('SL-0001')
        ->subtotal->toBe(10000)
        ->discount->toBe(500)
        ->tax->toBe(1900)
        ->total->toBe(11400)
        ->paid->toBe(5000)
        ->status->toBe(App\Enums\SaleStatusEnum::COMPLETED)
        ->notes->toBe('Thanks for your purchase')
        ->and($data->client->resolve())
        ->toBeInstanceOf(ClientData::class)
        ->id->toBe($client->id)
        ->and($data->store->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($sale->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($sale->updated_at->toDateTimeString());
});
