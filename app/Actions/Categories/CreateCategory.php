<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Data\Categories\CreateCategoryData;
use App\Models\Category;

final readonly class CreateCategory
{
    public function handle(CreateCategoryData $data): Category
    {
        return Category::query()->create([
            'name' => $data->name,
            'code' => $data->code,
            'type' => $data->type,
            'is_active' => $data->is_active,
            'created_by' => $data->created_by,
        ]);
    }
}
