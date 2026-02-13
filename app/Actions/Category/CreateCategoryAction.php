<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\EnsureUniqueSlugAction;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class CreateCategoryAction
{
    public function __construct(private EnsureUniqueSlugAction $ensureUniqueSlug) {}

    /**
     * @param  array{name: string, slug?: string, description?: string, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $name = $data['name'];
            if (! isset($data['slug'])) {
                $data['slug'] = Str::slug($name);
            }

            $data['slug'] = $this->ensureUniqueSlug->handle($data['slug'], Category::class);

            $data['is_active'] ??= true;

            return Category::query()->create($data)->refresh();
        });
    }
}
