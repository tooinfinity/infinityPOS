<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Data\Category\CreateCategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateCategory
{
    /**
     * @throws Throwable
     */
    public function handle(CreateCategoryData $data): Category
    {
        return DB::transaction(static fn (): Category => Category::query()->forceCreate([
            'name' => $data->name,
            'description' => $data->description,
            'is_active' => $data->is_active,
        ])->refresh());
    }
}
