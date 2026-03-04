<?php

declare(strict_types=1);

namespace App\Actions\Supplier;

use App\Data\Supplier\CreateSupplierData;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateSupplier
{
    /**
     * @throws Throwable
     */
    public function handle(CreateSupplierData $data): Supplier
    {
        return DB::transaction(static fn (): Supplier => Supplier::query()->forceCreate([
            'name' => $data->name,
            'company_name' => $data->company_name,
            'email' => $data->email,
            'phone' => $data->phone,
            'address' => $data->address,
            'city' => $data->city,
            'country' => $data->country,
            'is_active' => $data->is_active,
        ])->refresh());
    }
}
