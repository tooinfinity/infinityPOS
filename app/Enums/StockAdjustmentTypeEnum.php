<?php

declare(strict_types=1);

namespace App\Enums;

enum StockAdjustmentTypeEnum: string
{
    case EXPIRED = 'expired';
    case DAMAGED = 'damaged';
    case MANUAL = 'manual';
    case CORRECTION = 'correction';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function options(): array
    {
        return array_map(
            fn (StockAdjustmentTypeEnum $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::EXPIRED => 'Expired',
            self::DAMAGED => 'Damaged',
            self::MANUAL => 'Manual Adjustment',
            self::CORRECTION => 'Correction',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EXPIRED => 'red',
            self::DAMAGED => 'orange',
            self::MANUAL => 'blue',
            self::CORRECTION => 'purple',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXPIRED => 'calendar-x',
            self::DAMAGED => 'alert-triangle',
            self::MANUAL => 'edit',
            self::CORRECTION => 'check-circle',
        };
    }

    public function requiresReason(): bool
    {
        return true; // All adjustments require a reason
    }

    /**
     * Typical quantity direction (negative = removal)
     */
    public function isRemoval(): bool
    {
        return in_array($this, [self::EXPIRED, self::DAMAGED]);
    }
}
