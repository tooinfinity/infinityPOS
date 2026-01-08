<?php

declare(strict_types=1);

namespace App\Enums;

enum RefundMethodEnum: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case STORE_CREDIT = 'store_credit';

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
            fn (RefundMethodEnum $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::STORE_CREDIT => 'Store Credit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CASH => 'green',
            self::CARD => 'blue',
            self::STORE_CREDIT => 'purple',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CASH => 'banknote',
            self::CARD => 'credit-card',
            self::STORE_CREDIT => 'gift',
        };
    }
}
