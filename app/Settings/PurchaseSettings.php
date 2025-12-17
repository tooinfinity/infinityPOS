<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class PurchaseSettings extends Settings
{
    public bool $enable_purchase_returns;

    public bool $require_supplier_for_purchase;

    public bool $enable_purchase_notes;

    public static function group(): string
    {
        return 'purchase';
    }
}
