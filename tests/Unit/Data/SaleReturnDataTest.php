<?php

declare(strict_types=1);

use App\Data\ClientData;
use App\Data\SaleData;
use App\Data\SaleReturnData;
use App\Data\StoreData;
use App\Data\UserData;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Store;
use App\Models\User;

it('transforms a sale return model into SaleReturnData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $client = Client::factory()->create();
    $store = Store::factory()->create();
    $sale = Sale::factory()->create();

    /** @var SaleReturn $return */
    $return = SaleReturn::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($client, 'client')
        ->for($store, 'store')
        ->for($sale, 'sale')
        ->create([
            'reference' => 'SR-0001',
            'subtotal' => 5000,
            'discount' => 0,
            'tax' => 0,
            'total' => 5000,
            'refunded' => 3000,
            'status' => App\Enums\SaleReturnStatusEnum::PENDING->value,
            'reason' => 'Damaged',
            'notes' => 'Process refund',
        ]);

    $data = SaleReturnData::from(
        $return->load(['creator', 'updater', 'client', 'store', 'sale'])
    );

    expect($data)
        ->toBeInstanceOf(SaleReturnData::class)
        ->id->toBe($return->id)
        ->reference->toBe('SR-0001')
        ->subtotal->toBe(5000)
        ->discount->toBe(0)
        ->tax->toBe(0)
        ->total->toBe(5000)
        ->refunded->toBe(3000)
        ->status->toBe(App\Enums\SaleReturnStatusEnum::PENDING)
        ->reason->toBe('Damaged')
        ->notes->toBe('Process refund')
        ->and($data->sale->resolve())
        ->toBeInstanceOf(SaleData::class)
        ->id->toBe($sale->id)
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
        ->toBe($return->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($return->updated_at->toDateTimeString());
});
