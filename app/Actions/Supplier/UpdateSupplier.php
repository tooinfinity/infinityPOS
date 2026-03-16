<?php

declare(strict_types=1);

namespace App\Actions\Supplier;

use App\Data\Supplier\SupplierData;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateSupplier
{
    /**
     * @throws Throwable
     */
    public function handle(Supplier $supplier, SupplierData $data): Supplier
    {
        return DB::transaction(static function () use ($supplier, $data): Supplier {

            $supplier->update([
                'name' => $data->name,
                'company_name' => $data->company_name,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'city' => $data->city,
                'country' => $data->country,
                'is_active' => $data->is_active,
            ]);

            return $supplier->refresh();
        });
    }
}
