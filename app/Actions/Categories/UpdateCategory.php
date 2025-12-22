<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Data\Categories\UpdateCategoryData;
use App\Models\Category;

final readonly class UpdateCategory
{
    public function handle(Category $category, UpdateCategoryData $data): void
    {
        $updateData = array_filter([
            'name' => $data->name,
            'code' => $data->code,
            'type' => $data->type,
            'is_active' => $data->is_active,
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $category->update($updateData);
    }
}
