<?php

declare(strict_types=1);

namespace App\Actions\Taxes;

use App\Data\Taxes\UpdateTaxData;
use App\Models\Tax;

final readonly class UpdateTax
{
    public function handle(Tax $tax, UpdateTaxData $data): void
    {
        $updateData = array_filter([
            'name' => $data->name,
            'rate' => $data->rate,
            'tax_type' => $data->tax_type,
            'is_active' => $data->is_active,
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $tax->update($updateData);
    }
}
