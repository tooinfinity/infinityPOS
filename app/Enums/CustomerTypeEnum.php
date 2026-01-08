<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerTypeEnum: string
{
    case WALK_IN = 'walk-in';
    case REGULAR = 'regular';
    case BUSINESS = 'business';

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
            fn (CustomerTypeEnum $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::WALK_IN => 'Walk-in',
            self::REGULAR => 'Regular',
            self::BUSINESS => 'Business',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WALK_IN => 'gray',
            self::REGULAR => 'blue',
            self::BUSINESS => 'purple',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WALK_IN => 'user',
            self::REGULAR => 'user-check',
            self::BUSINESS => 'building',
        };
    }

    /**
     * Can create invoices (for B2B)
     */
    public function canCreateInvoices(): bool
    {
        return $this === self::BUSINESS;
    }

    /**
     * Requires detailed information
     */
    public function requiresDetails(): bool
    {
        return $this !== self::WALK_IN;
    }
}
