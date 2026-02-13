<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

final readonly class EnsureUniqueSlugAction
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function handle(string $slug, string $modelClass, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug, $modelClass, $excludeId)) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function slugExists(string $slug, string $modelClass, ?int $excludeId): bool
    {
        $query = $modelClass::query()->where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
