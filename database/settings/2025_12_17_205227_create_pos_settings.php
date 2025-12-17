<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('pos.enable_barcode_scanner', true);
        $this->migrator->add('pos.enable_receipt_printer', true);
        $this->migrator->add('pos.auto_print_receipt', false);
        $this->migrator->add('pos.default_payment_method', 'cash');
        $this->migrator->add('pos.enable_customer_display', false);
        $this->migrator->add('pos.receipt_header', '');
        $this->migrator->add('pos.receipt_footer', 'Thank you for your purchase!');
    }
};
