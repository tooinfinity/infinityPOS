<?php

declare(strict_types=1);

use App\Data\Stores\StoreData;
use App\Data\Users\UserData;
use App\Models\Store;
use App\Models\User;

it('transforms a store model into StoreData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Store $store */
    $store = Store::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->create([
            'name' => 'Main Store',
            'city' => 'Metropolis',
            'address' => '123 Market St',
            'phone' => '555-9876',
            'is_active' => true,
        ]);

    $data = StoreData::from(
        $store->load(['creator', 'updater'])
    );

    expect($data)
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id)
        ->name->toBe('Main Store')
        ->city->toBe('Metropolis')
        ->address->toBe('123 Market St')
        ->phone->toBe('555-9876')
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($store->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($store->updated_at->toDateTimeString());
});
