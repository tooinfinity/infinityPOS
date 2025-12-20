<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;

final readonly class DeleteCategory
{
    public function handle(Category $category): void
    {
        $category->update([
            'created_by' => null,
        ]);
        $category->delete();
    }
}
