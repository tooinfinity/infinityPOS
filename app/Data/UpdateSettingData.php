<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

final class UpdateSettingData extends Data
{
    public function __construct(
        #[Required, StringType, Exists('settings', 'key')]
        public string $key,

        public mixed $value = null,
    ) {}
}
