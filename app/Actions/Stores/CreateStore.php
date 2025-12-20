<?php

declare(strict_types=1);

namespace App\Actions\Stores;

use App\Data\Stores\CreateStoreData;
use App\Models\Store;

final readonly class CreateStore
{
    public function handle(CreateStoreData $data): Store
    {
        return Store::query()->create([
            'name' => $data->name,
            'city' => $data->city,
            'address' => $data->address,
            'phone' => $data->phone,
            'is_active' => $data->is_active,
            'created_by' => $data->created_by,
        ]);
    }
}
