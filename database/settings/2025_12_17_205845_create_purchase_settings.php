<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('purchase.enable_purchase_returns', true);
        $this->migrator->add('purchase.require_supplier_for_purchase', true);
        $this->migrator->add('purchase.enable_purchase_notes', true);
    }
};
