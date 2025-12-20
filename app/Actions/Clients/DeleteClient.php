<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use App\Models\Client;

final readonly class DeleteClient
{
    public function handle(Client $client): void
    {
        $client->update([
            'created_by' => null,
        ]);
        $client->delete();
    }
}
