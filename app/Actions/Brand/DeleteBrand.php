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
            $logo = $brand->logo;

            $brand->products()->each(function (Product $product): void {
                $product->forceFill(['brand_id' => null])->save();
            });

            $deleted = (bool) $brand->delete();

            if ($deleted && $logo !== null) {
                DB::afterCommit(static fn () => Storage::disk('public')->delete($logo));
            }

            return $deleted;
        });
    }
}
