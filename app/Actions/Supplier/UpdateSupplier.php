<?php

declare(strict_types=1);

namespace App\Actions\Supplier;

use App\Data\Supplier\UpdateSupplierData;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateSupplier
{
    /**
     * @throws Throwable
     */
    public function handle(Supplier $supplier, UpdateSupplierData $data): Supplier
    {
        return DB::transaction(static function () use ($supplier, $data): Supplier {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                $updateData['name'] = $data->name;
            }

            if (! $data->company_name instanceof Optional) {
                $updateData['company_name'] = $data->company_name;
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

            $supplier->update($updateData);

            return $supplier->refresh();
        });
    }
}
