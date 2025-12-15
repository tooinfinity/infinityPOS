<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Enums\SettingTypeEnum;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

final readonly class SeedPredefinedSettings
{
    /**
     * Seed predefined settings into the database.
     *
     * @return array{created: int, skipped: int, updated: int}
     */
    public function handle(bool $force = false): array
    {
        return DB::transaction(function () use ($force): array {
            $created = 0;
            $skipped = 0;
            $updated = 0;

            foreach ($this->getDefaultSettings() as $settingData) {
                $existing = Setting::query()->where('key', $settingData['key'])->first();

                if ($existing !== null && ! $force) {
                    $skipped++;

                    continue;
                }

                if ($existing !== null) {
                    $existing->update([
                        'value' => $settingData['value'],
                        'type' => $settingData['type'],
                        'group' => $settingData['group'],
                        'description' => $settingData['description'] ?? null,
                        'is_public' => $settingData['is_public'] ?? true,
                    ]);
                    $updated++;
                } else {
                    Setting::query()->create([
                        'key' => $settingData['key'],
                        'value' => $settingData['value'],
                        'type' => $settingData['type'],
                        'group' => $settingData['group'],
                        'description' => $settingData['description'] ?? null,
                        'is_public' => $settingData['is_public'] ?? true,
                    ]);
                    $created++;
                }
            }

            return ['created' => $created, 'skipped' => $skipped, 'updated' => $updated];
        });
    }

    /**
     * Get the total count of predefined settings.
     */
    public function getSettingsCount(): int
    {
        return count($this->getDefaultSettings());
    }

    /**
     * Get the default settings configuration.
     *
     * @return array<int, array{key: string, value: string, type: SettingTypeEnum, group: string, description?: string, is_public?: bool}>
     */
    private function getDefaultSettings(): array
    {
        return [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'My Store',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Store name displayed throughout the application',
            ],
            [
                'key' => 'app_timezone',
                'value' => 'UTC',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Default timezone for the application',
            ],
            [
                'key' => 'app_locale',
                'value' => 'en',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Default language/locale',
            ],
            [
                'key' => 'currency_code',
                'value' => 'USD',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Currency code (ISO 4217)',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Currency symbol for display',
            ],
            [
                'key' => 'currency_position',
                'value' => 'before',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Position of currency symbol (before/after amount)',
            ],
            [
                'key' => 'decimal_separator',
                'value' => '.',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Decimal separator for numbers',
            ],
            [
                'key' => 'thousand_separator',
                'value' => ',',
                'type' => SettingTypeEnum::STRING,
                'group' => 'general',
                'description' => 'Thousand separator for numbers',
            ],
            [
                'key' => 'decimal_places',
                'value' => '2',
                'type' => SettingTypeEnum::NUMBER,
                'group' => 'general',
                'description' => 'Number of decimal places for currency',
            ],

            // POS Settings
            [
                'key' => 'enable_barcode_scanner',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'pos',
                'description' => 'Enable barcode scanner support',
            ],
            [
                'key' => 'enable_receipt_printer',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'pos',
                'description' => 'Enable receipt printer support',
            ],
            [
                'key' => 'auto_print_receipt',
                'value' => '0',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'pos',
                'description' => 'Automatically print receipt after sale',
            ],
            [
                'key' => 'default_payment_method',
                'value' => 'cash',
                'type' => SettingTypeEnum::STRING,
                'group' => 'pos',
                'description' => 'Default payment method for new sales',
            ],
            [
                'key' => 'enable_customer_display',
                'value' => '0',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'pos',
                'description' => 'Enable customer-facing display',
            ],
            [
                'key' => 'receipt_header',
                'value' => '',
                'type' => SettingTypeEnum::STRING,
                'group' => 'pos',
                'description' => 'Custom text for receipt header',
            ],
            [
                'key' => 'receipt_footer',
                'value' => 'Thank you for your purchase!',
                'type' => SettingTypeEnum::STRING,
                'group' => 'pos',
                'description' => 'Custom text for receipt footer',
            ],

            // Inventory Settings
            [
                'key' => 'enable_batch_tracking',
                'value' => '0',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'inventory',
                'description' => 'Enable batch/lot number tracking',
            ],
            [
                'key' => 'enable_expiry_tracking',
                'value' => '0',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'inventory',
                'description' => 'Enable product expiry date tracking',
            ],
            [
                'key' => 'low_stock_threshold',
                'value' => '10',
                'type' => SettingTypeEnum::NUMBER,
                'group' => 'inventory',
                'description' => 'Default threshold for low stock alerts',
            ],
            [
                'key' => 'enable_stock_alerts',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'inventory',
                'description' => 'Enable low stock alert notifications',
            ],
            [
                'key' => 'auto_deduct_stock',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'inventory',
                'description' => 'Automatically deduct stock on sale completion',
            ],

            // Sales Settings
            [
                'key' => 'enable_discounts',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'sales',
                'description' => 'Allow discounts on sales',
            ],
            [
                'key' => 'max_discount_percentage',
                'value' => '100',
                'type' => SettingTypeEnum::NUMBER,
                'group' => 'sales',
                'description' => 'Maximum allowed discount percentage',
            ],
            [
                'key' => 'require_customer_for_sale',
                'value' => '0',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'sales',
                'description' => 'Require customer selection for sales',
            ],
            [
                'key' => 'enable_sale_notes',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'sales',
                'description' => 'Allow adding notes to sales',
            ],
            [
                'key' => 'enable_tax_calculation',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'sales',
                'description' => 'Enable automatic tax calculation',
            ],
            [
                'key' => 'tax_inclusive',
                'value' => '0',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'sales',
                'description' => 'Prices include tax by default',
            ],

            // Purchase Settings
            [
                'key' => 'enable_purchase_returns',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'purchase',
                'description' => 'Allow purchase returns to suppliers',
            ],
            [
                'key' => 'require_supplier_for_purchase',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'purchase',
                'description' => 'Require supplier selection for purchases',
            ],
            [
                'key' => 'enable_purchase_notes',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'purchase',
                'description' => 'Allow adding notes to purchases',
            ],

            // Reporting Settings
            [
                'key' => 'default_date_range',
                'value' => 'today',
                'type' => SettingTypeEnum::STRING,
                'group' => 'reporting',
                'description' => 'Default date range for reports (today/week/month/year)',
            ],
            [
                'key' => 'enable_profit_tracking',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'reporting',
                'description' => 'Track and display profit margins',
            ],
            [
                'key' => 'enable_export_reports',
                'value' => '1',
                'type' => SettingTypeEnum::BOOLEAN,
                'group' => 'reporting',
                'description' => 'Allow exporting reports to CSV/PDF',
            ],
        ];
    }
}
