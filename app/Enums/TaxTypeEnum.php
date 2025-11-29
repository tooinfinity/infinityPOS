<?php

declare(strict_types=1);

namespace App\Enums;

enum TaxTypeEnum: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (TaxTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage',
            self::FIXED => 'Fixed',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::PERCENTAGE => '%',
            self::FIXED => '',
        };
    }
}
