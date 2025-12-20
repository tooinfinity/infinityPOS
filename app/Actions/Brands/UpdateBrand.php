<?php

declare(strict_types=1);

namespace App\Actions\Brands;

use App\Data\Brands\UpdateBrandData;
use App\Models\Brand;

final readonly class UpdateBrand
{
    public function handle(Brand $brand, UpdateBrandData $data): void
    {
        $brand->update([
            'name' => $data->name,
            'is_active' => $data->is_active,
            'updated_by' => $data->updated_by,
        ]);
    }
}
