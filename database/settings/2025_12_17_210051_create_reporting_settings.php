<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('reporting.default_date_range', 'today');
        $this->migrator->add('reporting.enable_profit_tracking', true);
        $this->migrator->add('reporting.enable_export_reports', true);
    }
};
