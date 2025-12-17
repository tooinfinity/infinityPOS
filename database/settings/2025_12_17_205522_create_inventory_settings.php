<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('inventory.enable_batch_tracking', false);
        $this->migrator->add('inventory.enable_expiry_tracking', false);
        $this->migrator->add('inventory.low_stock_threshold', 10);
        $this->migrator->add('inventory.enable_stock_alerts', true);
        $this->migrator->add('inventory.auto_deduct_stock', true);
    }
};
