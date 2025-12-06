<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case SALE = 'sale';
    case PURCHASE = 'purchase';
    case EXPENSE = 'expense';
    case OTHER = 'other';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (PaymentTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::SALE => 'Sale',
            self::PURCHASE => 'Purchase',
            self::EXPENSE => 'Expense',
            self::OTHER => 'Other',
        };
    }
}
