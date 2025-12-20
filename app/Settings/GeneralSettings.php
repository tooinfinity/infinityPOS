<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class GeneralSettings extends Settings
{
    public string $app_name;

    public string $app_timezone;

    public string $app_locale;

    public string $currency_code;

    public string $currency_symbol;

    public string $currency_position;

    public string $decimal_separator;

    public string $thousand_separator;

    public int $decimal_places;

    public string $article_number;

    public string $tax_identification_number;

    public string $business_register_number;

    public string $statistical_identification_number;

    public string $bank_account_details;

    public static function group(): string
    {
        return 'general';
    }
}
