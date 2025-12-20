<?php

declare(strict_types=1);

use App\Actions\Stores\CreateStore;
use App\Data\Stores\CreateStoreData;
use App\Models\Store;
use App\Models\User;

it('may create a store', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateStore::class);

    $data = CreateStoreData::from([
        'name' => 'Main Store',
        'city' => 'New York',
        'address' => '123 Main St',
        'phone' => '555-1234',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $store = $action->handle($data);

    expect($store)->toBeInstanceOf(Store::class)
        ->and($store->name)->toBe('Main Store')
        ->and($store->city)->toBe('New York')
        ->and($store->address)->toBe('123 Main St')
        ->and($store->phone)->toBe('555-1234')
        ->and($store->is_active)->toBeTrue()
        ->and($store->created_by)->toBe($user->id);
});
