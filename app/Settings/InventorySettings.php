<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class InventorySettings extends Settings
{
    public bool $enable_batch_tracking;

    public bool $enable_expiry_tracking;

    public int $low_stock_threshold;

    public bool $enable_stock_alerts;

    public bool $auto_deduct_stock;

    public static function group(): string
    {
        return 'inventory';
    }
}
