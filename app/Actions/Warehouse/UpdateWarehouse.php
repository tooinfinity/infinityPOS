<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Data\Warehouse\WarehouseData;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateWarehouse
{
    /**
     * @throws Throwable
     */
    public function handle(Warehouse $warehouse, WarehouseData $data): Warehouse
    {
        return DB::transaction(static function () use ($warehouse, $data): Warehouse {
            $updateData = [
                'name' => $data->name ?? $warehouse->name,
                'code' => $data->code ?? $warehouse->code,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address ?? $warehouse->address,
                'city' => $data->city ?? $warehouse->city,
                'country' => $data->country ?? $warehouse->country,
                'is_active' => $data->is_active ?? $warehouse->is_active,
            ];

            $warehouse->update($updateData);

            return $warehouse->refresh();
        });
    }
}
