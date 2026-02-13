<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\EnsureUniqueSlugAction;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class UpdateCategoryAction
{
    public function __construct(private EnsureUniqueSlugAction $ensureUniqueSlug) {}

    /**
     * @param  array{name?: string, slug?: string, description?: string, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            if (isset($data['name']) && $data['name'] !== $category->name && ! isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            if (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug->handle($data['slug'], Category::class, $category->id);
            }

            $category->update($data);

            return $category->refresh();
        });
    }
}
