<?php

declare(strict_types=1);

namespace App\Actions\Brands;

use App\Models\Brand;

final readonly class DeleteBrand
{
    public function handle(Brand $brand): void
    {
        $brand->update([
            'created_by' => null,
        ]);
        $brand->delete();
    }
}
