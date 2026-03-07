<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\UploadImage;
use App\Data\Brand\CreateBrandData;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateBrand
{
    /**
     * @throws Throwable
     */
    public function handle(CreateBrandData $data): Brand
    {

        return DB::transaction(static fn (): Brand => Brand::query()->forceCreate([
            'name' => $data->name,
            'is_active' => $data->is_active,
        ])->refresh());
    }
}
