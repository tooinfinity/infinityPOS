<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sales.enable_discounts', true);
        $this->migrator->add('sales.max_discount_percentage', 100);
        $this->migrator->add('sales.require_customer_for_sale', false);
        $this->migrator->add('sales.enable_sale_notes', true);
        $this->migrator->add('sales.enable_tax_calculation', true);
        $this->migrator->add('sales.tax_inclusive', false);
    }
};
