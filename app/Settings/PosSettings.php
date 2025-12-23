<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class PosSettings extends Settings
{
    public bool $enable_barcode_scanner = true;

    public bool $enable_receipt_printer = true;

    public bool $auto_print_receipt = false;

    public string $default_payment_method = 'cash';

    public bool $enable_customer_display = false;

    public bool $require_cash_drawer_for_cash_payments = false;

    public string $receipt_header = '';

    public string $receipt_footer = 'Thank you for your purchase!';

    public static function group(): string
    {
        return 'pos';
    }
}
