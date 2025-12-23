<?php

declare(strict_types=1);

namespace App\Data\Settings;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class PosSettingsData extends Data
{
    public function __construct(
        #[Required, BooleanType]
        public bool $enable_barcode_scanner,
        #[Required, BooleanType]
        public bool $enable_receipt_printer,
        #[Required, BooleanType]
        public bool $auto_print_receipt,
        #[Required]
        public string $default_payment_method,
        #[Required, BooleanType]
        public bool $enable_customer_display,
        #[Required, BooleanType]
        public bool $require_cash_drawer_for_cash_payments,
        public string $receipt_header,
        public string $receipt_footer,
    ) {}
}
