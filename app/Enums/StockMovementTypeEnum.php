<?php

declare(strict_types=1);

namespace App\Enums;

enum StockMovementTypeEnum: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';
    case Transfer = 'transfer';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            static fn (StockMovementTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::In => 'In',
            self::Out => 'Out',
            self::Adjustment => 'Adjustment',
            self::Transfer => 'Transfer',
        };
    }
}
