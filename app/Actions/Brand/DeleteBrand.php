<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteBrand
{
    /**
     * @throws Throwable
     */
    public function handle(Brand $brand): bool
    {
        return DB::transaction(static function () use ($brand): bool {

            $brand->products()->each(function (Product $product): void {
                $product->forceFill(['brand_id' => null])->save();
            });

            return (bool) $brand->delete();
        });
    }
}
