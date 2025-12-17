<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class SalesSettings extends Settings
{
    public bool $enable_discounts;

    public int $max_discount_percentage;

    public bool $require_customer_for_sale;

    public bool $enable_sale_notes;

    public bool $enable_tax_calculation;

    public bool $tax_inclusive;

    public static function group(): string
    {
        return 'sales';
    }
}
