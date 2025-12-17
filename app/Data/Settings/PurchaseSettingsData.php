<?php

declare(strict_types=1);

namespace App\Data\Settings;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class PurchaseSettingsData extends Data
{
    public function __construct(
        #[Required, BooleanType]
        public bool $enable_purchase_returns,
        #[Required, BooleanType]
        public bool $require_supplier_for_purchase,
        #[Required, BooleanType]
        public bool $enable_purchase_notes,
    ) {}
}
