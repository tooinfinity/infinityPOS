<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\EnsureUniqueSlug;
use App\Data\Category\UpdateCategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateCategory
{
    public function __construct(private EnsureUniqueSlug $ensureUniqueSlug) {}

    /**
     * @throws Throwable
     */
    public function handle(Category $category, UpdateCategoryData $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                if ($data->name !== $category->name && $data->slug instanceof Optional) {
                    $updateData['slug'] = $this->ensureUniqueSlug->handle(Str::slug($data->name), Category::class, $category->id);
                }
                $updateData['name'] = $data->name;
            }

            if (! $data->slug instanceof Optional) {
                $updateData['slug'] = $this->ensureUniqueSlug->handle($data->slug, Category::class, $category->id);
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
