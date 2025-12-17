<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ReportingSettings extends Settings
{
    public string $default_date_range;

    public bool $enable_profit_tracking;

    public bool $enable_export_reports;

    public static function group(): string
    {
        return 'reporting';
    }
}
