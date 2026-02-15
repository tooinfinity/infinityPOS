<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteCategory
{
    /**
     * @throws Throwable
     */
    public function handle(Category $category): bool
    {
        return DB::transaction(static function () use ($category): bool {
            $category->products()->each(function (Product $product): void {
                $product->forceFill(['category_id' => null])->save();
            });

            return (bool) $category->delete();
        });
    }
}
