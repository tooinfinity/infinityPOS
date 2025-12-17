<?php

declare(strict_types=1);

namespace App\Data\Settings;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class GeneralSettingsData extends Data
{
    public function __construct(
        #[Required]
        public string $app_name,
        #[Required]
        public string $app_timezone,
        #[Required]
        public string $app_locale,
        #[Required]
        public string $currency_code,
        #[Required]
        public string $currency_symbol,
        #[Required, In(['before', 'after'])]
        public string $currency_position,
        #[Required]
        public string $decimal_separator,
        #[Required]
        public string $thousand_separator,
        #[Required, IntegerType, Min(0), Max(6)]
        public int $decimal_places,
    ) {}
}
