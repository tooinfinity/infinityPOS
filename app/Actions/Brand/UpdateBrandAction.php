<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\EnsureUniqueSlugAction;
use App\Actions\UploadImageAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final readonly class UpdateBrandAction
{
    public function __construct(
        private EnsureUniqueSlugAction $ensureUniqueSlug,
        private UploadImageAction $uploadImage,
    ) {}

    /**
     * @param  array{name?: string, slug?: string, logo?: UploadedFile|string|null, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(Brand $brand, array $data): Brand
    {
        return DB::transaction(function () use ($brand, $data): Brand {
            if (isset($data['name']) && $data['name'] !== $brand->name && ! isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            if (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug->handle($data['slug'], Brand::class, $brand->id);
            }

            if (array_key_exists('logo', $data)) {
                if ($data['logo'] instanceof UploadedFile) {
                    $data['logo'] = $this->uploadImage->handle($data['logo'], 'brands', $brand->logo);
                } elseif ($data['logo'] === null && $brand->logo !== null) {
                    Storage::disk('public')->delete($brand->logo);
                }
            }

            $brand->update($data);

            return $brand->refresh();
        });
    }
}
