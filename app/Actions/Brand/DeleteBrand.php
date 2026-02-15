<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

            if ($brand->logo !== null) {
                Storage::disk('public')->delete($brand->logo);
            }

            return (bool) $brand->delete();
        });
    }
}
