<?php

declare(strict_types=1);

use App\Actions\Clients\DeleteClient;
use App\Models\Client;
use App\Models\User;

it('may delete a client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteClient::class);
    $action->handle($client);

    expect(Client::query()->find($client->id))->toBeNull()
        ->and($client->created_by)->toBeNull();
});
