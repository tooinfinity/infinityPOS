<?php

declare(strict_types=1);

namespace App\Enums;

enum StockMovementTypeEnum: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case SALE_RETURN = 'sale_return';
    case PURCHASE_RETURN = 'purchase_return';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER = 'transfer';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (StockMovementTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::SALE_RETURN => 'Sale Return',
            self::PURCHASE_RETURN => 'Purchase Return',
            self::ADJUSTMENT => 'Adjustment',
            self::TRANSFER => 'Transfer',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PURCHASE => 'blue',
            self::SALE => 'green',
            self::SALE_RETURN => 'orange',
            self::PURCHASE_RETURN => 'red',
            self::ADJUSTMENT => 'purple',
            self::TRANSFER => 'indigo',
        };
    }

    public function isIncoming(): bool
    {
        return match ($this) {
            self::PURCHASE, self::SALE_RETURN, self::TRANSFER => true,
            self::SALE, self::PURCHASE_RETURN, self::ADJUSTMENT => false,
        };
    }
}
