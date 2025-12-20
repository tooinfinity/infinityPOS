<?php

declare(strict_types=1);

use App\Actions\Stores\DeleteStore;
use App\Models\Store;
use App\Models\User;

it('may delete a store', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteStore::class);
    $action->handle($store);

    expect(Store::query()->find($store->id))->toBeNull()
        ->and($store->created_by)->toBeNull();
});
