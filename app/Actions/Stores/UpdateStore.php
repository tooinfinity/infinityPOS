<?php

declare(strict_types=1);

namespace App\Actions\Stores;

use App\Data\Stores\UpdateStoreData;
use App\Models\Store;

final readonly class UpdateStore
{
    public function handle(Store $store, UpdateStoreData $data): void
    {
        $updateData = array_filter([
            'name' => $data->name,
            'city' => $data->city,
            'address' => $data->address,
            'phone' => $data->phone,
            'is_active' => $data->is_active,
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $store->update($updateData);
    }
}
