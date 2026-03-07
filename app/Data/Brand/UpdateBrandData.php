<?php

declare(strict_types=1);

namespace App\Data\Brand;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateBrandData extends Data
{
    public function __construct(
        public string|Optional $name,
        public bool|Optional $is_active,
    ) {}
}
