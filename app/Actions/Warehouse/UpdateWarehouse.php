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
            $warehouse->update([
                'name' => $data->name,
                'code' => $data->code,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'city' => $data->city,
                'country' => $data->country,
                'is_active' => $data->is_active,
            ]);

            return $warehouse->refresh();
        });
    }
}
