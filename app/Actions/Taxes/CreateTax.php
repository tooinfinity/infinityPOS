<?php

declare(strict_types=1);

namespace App\Actions\Taxes;

use App\Data\Taxes\CreateTaxData;
use App\Models\Tax;

final readonly class CreateTax
{
    public function handle(CreateTaxData $data): Tax
    {
        return Tax::query()->create([
            'name' => $data->name,
            'rate' => $data->rate,
            'tax_type' => $data->tax_type,
            'is_active' => $data->is_active,
            'created_by' => $data->created_by,
        ]);
    }
}
