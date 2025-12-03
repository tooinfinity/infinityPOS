<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $client = Client::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($client->toArray()))
        ->toBe([
            'id',
            'name',
            'phone',
            'email',
            'address',
            'balance',
            'is_active',
            'business_identifier_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
