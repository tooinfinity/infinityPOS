<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.app_name', 'infinityPOS');
        $this->migrator->add('general.app_timezone', 'UTC');
        $this->migrator->add('general.app_locale', 'en');
        $this->migrator->add('general.currency_code', 'USD');
        $this->migrator->add('general.currency_symbol', '$');
        $this->migrator->add('general.currency_position', 'before');
        $this->migrator->add('general.decimal_separator', '.');
        $this->migrator->add('general.thousand_separator', ',');
        $this->migrator->add('general.decimal_places', 2);
        $this->migrator->add('general.article_number', 'ART');
        $this->migrator->add('general.tax_identification_number', 'NIF');
        $this->migrator->add('general.business_register_number', 'RC');
        $this->migrator->add('general.statistical_identification_number', 'NIS');
        $this->migrator->add('general.bank_account_details', 'RIB');
    }
};
