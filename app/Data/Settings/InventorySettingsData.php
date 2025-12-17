<?php

declare(strict_types=1);

namespace App\Data\Settings;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class InventorySettingsData extends Data
{
    public function __construct(
        #[Required, BooleanType]
        public bool $enable_batch_tracking,
        #[Required, BooleanType]
        public bool $enable_expiry_tracking,
        #[Required, IntegerType, Min(0)]
        public int $low_stock_threshold,
        #[Required, BooleanType]
        public bool $enable_stock_alerts,
        #[Required, BooleanType]
        public bool $auto_deduct_stock,
    ) {}
}
