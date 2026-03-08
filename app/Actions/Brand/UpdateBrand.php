<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Data\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateBrand
{
    /**
     * @throws Throwable
     */
    public function handle(Brand $brand, BrandData $data): Brand
    {
        return DB::transaction(static function () use ($brand, $data): Brand {
            $brand->update([
                'name' => $data->name ?? $brand->name,
                'is_active' => $data->is_active ?? $brand->is_active,
            ]);

            return $brand->refresh();
        });
    }
}
