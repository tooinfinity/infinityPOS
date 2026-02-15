<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\EnsureUniqueSlug;
use App\Data\Category\CreateCategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class CreateCategory
{
    public function __construct(private EnsureUniqueSlug $ensureUniqueSlug) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateCategoryData $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $slug = $data->slug ?? Str::slug($data->name);
            $slug = $this->ensureUniqueSlug->handle($slug, Category::class);
            $isActive = $data->is_active ?? true;

            return Category::query()->create([
                'name' => $data->name,
                'slug' => $slug,
                'description' => $data->description,
                'is_active' => $isActive,
            ])->refresh();
        });
    }
}
