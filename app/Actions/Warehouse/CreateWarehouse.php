<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Data\Warehouse\CreateWarehouseData;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateWarehouse
{
    /**
     * @throws Throwable
     */
    public function handle(CreateWarehouseData $data): Warehouse
    {
        return DB::transaction(static fn (): Warehouse => Warehouse::query()->forceCreate([
            'name' => $data->name,
            'code' => $data->code,
            'email' => $data->email,
            'phone' => $data->phone,
            'address' => $data->address,
            'city' => $data->city,
            'country' => $data->country,
            'is_active' => $data->is_active ?? true,
        ]))->refresh();
    }
}
