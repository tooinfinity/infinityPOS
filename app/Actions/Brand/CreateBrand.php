<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\EnsureUniqueSlug;
use App\Actions\UploadImage;
use App\Data\Brand\CreateBrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class CreateBrand
{
    public function __construct(
        private EnsureUniqueSlug $ensureUniqueSlug,
        private UploadImage $uploadImage,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateBrandData $data): Brand
    {
        return DB::transaction(function () use ($data): Brand {
            $slug = $data->slug ?? Str::slug($data->name);
            $slug = $this->ensureUniqueSlug->handle($slug, Brand::class);
            $isActive = $data->is_active ?? true;

            $logo = $data->logo;
            if ($logo instanceof UploadedFile) {
                $logo = $this->uploadImage->handle($logo, 'brands');
            }

            return Brand::query()->create([
                'name' => $data->name,
                'slug' => $slug,
                'logo' => $logo,
                'is_active' => $isActive,
            ])->refresh();
        });
    }
}
