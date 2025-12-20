<?php

declare(strict_types=1);

use App\Actions\Clients\CreateClient;
use App\Data\Clients\CreateClientData;
use App\Models\BusinessIdentifier;
use App\Models\Client;
use App\Models\User;

it('may create a client', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateClient::class);

    $data = CreateClientData::from([
        'name' => 'John Doe',
        'phone' => '+1234567890',
        'email' => 'john@example.com',
        'address' => '123 Main St, New York',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $client = $action->handle($data);

    expect($client)->toBeInstanceOf(Client::class)
        ->and($client->name)->toBe('John Doe')
        ->and($client->phone)->toBe('+1234567890')
        ->and($client->email)->toBe('john@example.com')
        ->and($client->address)->toBe('123 Main St, New York')
        ->and($client->is_active)->toBeTrue()
        ->and($client->created_by)->toBe($user->id);
});
