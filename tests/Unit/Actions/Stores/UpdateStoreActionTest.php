<?php

declare(strict_types=1);

use App\Actions\Stores\UpdateStore;
use App\Data\Stores\UpdateStoreData;
use App\Models\Store;
use App\Models\User;

it('may update a store', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create([
        'name' => 'Old Store',
        'city' => 'Old City',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $action = resolve(UpdateStore::class);

    $data = UpdateStoreData::from([
        'name' => 'Updated Store',
        'city' => 'New City',
        'address' => 'New Address',
        'phone' => '555-9999',
        'is_active' => false,
        'updated_by' => $user2->id,
    ]);

    $action->handle($store, $data);

    expect($store->refresh()->name)->toBe('Updated Store')
        ->and($store->city)->toBe('New City')
        ->and($store->is_active)->toBeFalse()
        ->and($store->updated_by)->toBe($user2->id);
});
