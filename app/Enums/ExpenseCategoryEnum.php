<?php

declare(strict_types=1);

namespace App\Enums;

enum ExpenseCategoryEnum: string
{
    case UTILITIES = 'utilities';
    case SUPPLIES = 'supplies';
    case MAINTENANCE = 'maintenance';
    case OTHER = 'other';

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
            fn (ExpenseCategoryEnum $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::UTILITIES => 'Utilities',
            self::SUPPLIES => 'Supplies',
            self::MAINTENANCE => 'Maintenance',
            self::OTHER => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UTILITIES => 'blue',
            self::SUPPLIES => 'green',
            self::MAINTENANCE => 'orange',
            self::OTHER => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::UTILITIES => 'zap',
            self::SUPPLIES => 'package',
            self::MAINTENANCE => 'wrench',
            self::OTHER => 'more-horizontal',
        };
    }
}
