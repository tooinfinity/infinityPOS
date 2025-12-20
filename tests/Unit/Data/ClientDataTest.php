<?php

declare(strict_types=1);

use App\Data\BusinessIdentifierData;
use App\Data\Clients\ClientData;
use App\Data\Users\UserData;
use App\Models\BusinessIdentifier;
use App\Models\Client;
use App\Models\User;

it('transforms a client model into ClientData', function (): void {

    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Client $client */
    $client = Client::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->create([
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => 'john@example.com',
            'address' => 'Main street 1',
            'is_active' => true,
        ]);

    $data = ClientData::from(
        $client->load([
            'creator',
            'updater',
        ])
    );

    expect($data)
        ->toBeInstanceOf(ClientData::class)
        ->id->toBe($client->id)
        ->name->toBe('John Doe')
        ->phone->toBe('123456789')
        ->email->toBe('john@example.com')
        ->address->toBe('Main street 1')
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($client->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($client->updated_at->toDateTimeString());

});
