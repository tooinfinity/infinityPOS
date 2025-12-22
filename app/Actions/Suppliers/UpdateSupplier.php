<?php

declare(strict_types=1);

namespace App\Actions\Suppliers;

use App\Data\Suppliers\UpdateSupplierData;
use App\Models\Supplier;

final readonly class UpdateSupplier
{
    public function handle(Supplier $supplier, UpdateSupplierData $data): void
    {
        $updateData = array_filter([
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
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $supplier->update($updateData);
    }
}
