<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\UploadImage;
use App\Data\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class CreateBrand
{
    public function __construct(
        private UploadImage $uploadImage,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(BrandData $data): Brand
    {
        $logo = $data->logo;
        if ($logo instanceof UploadedFile) {
            $logo = $this->uploadImage->handle($logo, 'brands');
        }

        try {
            return DB::transaction(static fn (): Brand => Brand::query()->forceCreate([
                'name' => $data->name,
                'slug' => $data->slug,
                'logo' => $logo,
                'is_active' => $data->is_active,
            ])->refresh());
        } catch (Throwable $e) {
            if (is_string($logo) && $logo !== ($data->logo ?? null)) {
                Storage::disk('public')->delete($logo);
            }

            throw $e;
        }
    }
}
