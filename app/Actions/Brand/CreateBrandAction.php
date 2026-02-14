<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\EnsureUniqueSlugAction;
use App\Actions\UploadImageAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class CreateBrandAction
{
    public function __construct(
        private EnsureUniqueSlugAction $ensureUniqueSlug,
        private UploadImageAction $uploadImage,
    ) {}

    /**
     * @param  array{name: string, slug?: string, logo?: UploadedFile|string, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): Brand
    {
        return DB::transaction(function () use ($data): Brand {
            $name = $data['name'];
            if (! isset($data['slug'])) {
                $data['slug'] = Str::slug($name);
            }

            $data['slug'] = $this->ensureUniqueSlug->handle($data['slug'], Brand::class);

            $data['is_active'] ??= true;

            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $data['logo'] = $this->uploadImage->handle($data['logo'], 'brands');
            }

            return Brand::query()->create($data)->refresh();
        });
    }
}
