<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class BulkUpdateSettingsData extends Data
{
    /**
     * @param  array<int, UpdateSettingData>  $settings
     */
    public function __construct(
        #[Required, DataCollectionOf(UpdateSettingData::class)]
        public array $settings,
    ) {}
}
