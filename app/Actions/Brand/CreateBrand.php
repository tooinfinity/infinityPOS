<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\EnsureUniqueSlug;
use App\Actions\UploadImage;
use App\Data\Brand\CreateBrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $logo = $data->logo;
        if ($logo instanceof UploadedFile) {
            $logo = $this->uploadImage->handle($logo, 'brands');
        }

        try {
            return DB::transaction(function () use ($data, $logo): Brand {
                $slug = $data->slug ?? Str::slug($data->name);
                $slug = $this->ensureUniqueSlug->handle($slug, Brand::class);
                $isActive = $data->is_active ?? true;

                return Brand::query()->forceCreate([
                    'name' => $data->name,
                    'slug' => $slug,
                    'logo' => $logo,
                    'is_active' => $isActive,
                ])->refresh();
            });
        } catch (Throwable $e) {
            if (is_string($logo) && $logo !== ($data->logo ?? null)) {
                Storage::disk('public')->delete($logo);
            }

            throw $e;
        }
    }
}
