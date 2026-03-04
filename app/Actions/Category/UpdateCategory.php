<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Data\Category\UpdateCategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateCategory
{
    /**
     * @throws Throwable
     */
    public function handle(Category $category, UpdateCategoryData $data): Category
    {
        return DB::transaction(static function () use ($category, $data): Category {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                $updateData['name'] = $data->name;
            }

            if (! $data->slug instanceof Optional) {
                $updateData['slug'] = $data->slug;
            }

            if (! $data->description instanceof Optional) {
                $updateData['description'] = $data->description;
            }

            if (! $data->is_active instanceof Optional) {
                $updateData['is_active'] = $data->is_active;
            }

            $category->update($updateData);

            return $category->refresh();
        });
    }
}
