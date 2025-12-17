<?php

declare(strict_types=1);

namespace App\Data\Settings;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class ReportingSettingsData extends Data
{
    public function __construct(
        #[Required]
        public string $default_date_range,
        #[Required, BooleanType]
        public bool $enable_profit_tracking,
        #[Required, BooleanType]
        public bool $enable_export_reports,
    ) {}
}
