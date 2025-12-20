<?php

declare(strict_types=1);

namespace App\Actions\Suppliers;

use App\Data\Suppliers\CreateSupplierData;
use App\Models\Supplier;

final readonly class CreateSupplier
{
    public function handle(CreateSupplierData $data): Supplier
    {
        return Supplier::query()->create([
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
