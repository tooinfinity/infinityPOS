<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use App\Data\Clients\UpdateClientData;
use App\Models\Client;

final readonly class UpdateClient
{
    public function handle(Client $client, UpdateClientData $data): void
    {
        $updateData = array_filter([
            'name' => $data->name,
            'phone' => $data->phone,
            'email' => $data->email,
            'address' => $data->address,
            'article' => $data->article,
            'nif' => $data->nif,
            'nis' => $data->nis,
            'rc' => $data->rc,
            'rib' => $data->rib,
            'is_active' => $data->is_active,
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $client->update($updateData);
    }
}
