<?php

declare(strict_types=1);

namespace App\Actions\Warehouse;

use App\Data\Warehouse\UpdateWarehouseData;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateWarehouse
{
    /**
     * @throws Throwable
     */
    public function handle(Warehouse $warehouse, UpdateWarehouseData $data): void
    {
        DB::transaction(static function () use ($warehouse, $data): void {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                $updateData['name'] = $data->name;
            }
            if (! $data->code instanceof Optional) {
                $updateData['code'] = $data->code;
            }
            if (! $data->email instanceof Optional) {
                $updateData['email'] = $data->email;
            }
            if (! $data->phone instanceof Optional) {
                $updateData['phone'] = $data->phone;
            }
            if (! $data->address instanceof Optional) {
                $updateData['address'] = $data->address;
            }
            if (! $data->city instanceof Optional) {
                $updateData['city'] = $data->city;
            }
            if (! $data->country instanceof Optional) {
                $updateData['country'] = $data->country;
            }
            if (! $data->is_active instanceof Optional) {
                $updateData['is_active'] = $data->is_active;
            }

            $warehouse->update($updateData);
        });
    }
}
