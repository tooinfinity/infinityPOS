<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Data\Category\CategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateCategory
{
    /**
     * @throws Throwable
     */
    public function handle(Category $category, CategoryData $data): Category
    {
        return DB::transaction(static function () use ($category, $data): Category {
            $category->update([
                'name' => $data->name ?? $category->name,
                'description' => $data->description ?? $category->description,
                'is_active' => $data->is_active ?? $category->is_active,
            ]);

            return $category->refresh();
        });
    }
}
