<?php

declare(strict_types=1);

use App\Actions\Clients\UpdateClient;
use App\Data\Clients\UpdateClientData;
use App\Models\BusinessIdentifier;
use App\Models\Client;
use App\Models\User;

it('may update a client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create([
        'name' => 'Old Client',
        'phone' => '+1111111111',
        'email' => 'old@example.com',
        'address' => 'Old Address',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $action = resolve(UpdateClient::class);

    $data = UpdateClientData::from([
        'name' => 'Updated Client',
        'phone' => '+9999999999',
        'email' => 'updated@example.com',
        'address' => 'Updated Address',
        'is_active' => false,
        'updated_by' => $user2->id,
    ]);

    $action->handle($client, $data);

    expect($client->refresh()->name)->toBe('Updated Client')
        ->and($client->phone)->toBe('+9999999999')
        ->and($client->email)->toBe('updated@example.com')
        ->and($client->address)->toBe('Updated Address')
        ->and($client->is_active)->toBeFalse()
        ->and($client->updated_by)->toBe($user2->id);
});
