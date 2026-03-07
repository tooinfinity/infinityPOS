<?php

declare(strict_types=1);

namespace App\Data\Brand;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

final class CreateBrandData extends Data
{
    public function __construct(
        public string $name,
        public bool $is_active,
    ) {}
}
