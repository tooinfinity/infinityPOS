<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\EnsureUniqueSlug;
use App\Actions\UploadImage;
use App\Data\Brand\UpdateBrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateBrand
{
    public function __construct(
        private EnsureUniqueSlug $ensureUniqueSlug,
        private UploadImage $uploadImage,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Brand $brand, UpdateBrandData $data): Brand
    {
        return DB::transaction(function () use ($brand, $data): Brand {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                if ($data->name !== $brand->name && $data->slug instanceof Optional) {
                    $updateData['slug'] = $this->ensureUniqueSlug->handle(Str::slug($data->name), Brand::class, $brand->id);
                }
                $updateData['name'] = $data->name;
            }

            if (! $data->slug instanceof Optional) {
                $updateData['slug'] = $this->ensureUniqueSlug->handle($data->slug, Brand::class, $brand->id);
            }

            if (! $data->is_active instanceof Optional) {
                $updateData['is_active'] = $data->is_active;
            }

            if (! $data->logo instanceof Optional) {
                $logo = $data->logo;
                if ($logo instanceof UploadedFile) {
                    $updateData['logo'] = $this->uploadImage->handle($logo, 'brands', $brand->logo);
                } elseif (is_string($logo)) {
                    $updateData['logo'] = $logo;
                } elseif ($brand->logo !== null) {
                    Storage::disk('public')->delete($brand->logo);
                    $updateData['logo'] = null;
                }
            }

            $brand->update($updateData);

            return $brand->refresh();
        });
    }
}
