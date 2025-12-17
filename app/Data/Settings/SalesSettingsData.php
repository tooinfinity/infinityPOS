<?php

declare(strict_types=1);

namespace App\Data\Settings;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class SalesSettingsData extends Data
{
    public function __construct(
        #[Required, BooleanType]
        public bool $enable_discounts,
        #[Required, IntegerType, Min(0), Max(100)]
        public int $max_discount_percentage,
        #[Required, BooleanType]
        public bool $require_customer_for_sale,
        #[Required, BooleanType]
        public bool $enable_sale_notes,
        #[Required, BooleanType]
        public bool $enable_tax_calculation,
        #[Required, BooleanType]
        public bool $tax_inclusive,
    ) {}
}
