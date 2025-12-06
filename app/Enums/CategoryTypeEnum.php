<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryTypeEnum: string
{
    case PRODUCT = 'product';
    case EXPENSE = 'expense';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (CategoryTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT => 'Product',
            self::EXPENSE => 'Expense',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PRODUCT => 'cube',
            self::EXPENSE => 'receipt',
        };
    }
}
