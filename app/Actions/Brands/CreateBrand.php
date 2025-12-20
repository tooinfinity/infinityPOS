<?php

declare(strict_types=1);

namespace App\Actions\Brands;

use App\Data\Brands\CreateBrandData;
use App\Models\Brand;

final readonly class CreateBrand
{
    public function handle(CreateBrandData $data): Brand
    {
        return Brand::query()->create([
            'name' => $data->name,
            'is_active' => $data->is_active,
            'created_by' => $data->created_by,
        ]);
    }
}
