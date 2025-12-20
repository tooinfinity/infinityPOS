<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use App\Data\Clients\CreateClientData;
use App\Models\Client;

final readonly class CreateClient
{
    public function handle(CreateClientData $data): Client
    {
        return Client::query()->create([
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
            'created_by' => $data->created_by,
        ]);
    }
}
