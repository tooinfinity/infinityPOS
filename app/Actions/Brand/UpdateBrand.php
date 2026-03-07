<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\UploadImage;
use App\Data\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class UpdateBrand
{
    public function __construct(
        private UploadImage $uploadImage,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Brand $brand, BrandData $data): Brand
    {
        return DB::transaction(function () use ($brand, $data): Brand {
            $brand->update([
                'name' => $data['name'] ?? $brand->name,
                'slug' => $data['slug'] ?? $brand->slug,
                'is_active' => $data['is_active'] ?? $brand->is_active,
                'logo' => $data['logo'] ?? $brand->logo,
            ]);

            if ($brand->logo === null && $data->logo !== null) {
                $brand->update(['logo' => $this->uploadImage->handle($data->logo, 'brands')]);
            } elseif ($data->logo !== null && $data->logo !== $brand->logo) {
                $brand->update(['logo' => $this->uploadImage->handle($data->logo, 'brands')]);
            } else {
                DB::afterCommit(static fn () => Storage::disk('public')->delete($brand->logo));
            }

            return $brand->refresh();
        });
    }
}
