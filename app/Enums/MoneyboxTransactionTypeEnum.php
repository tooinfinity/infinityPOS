<?php

declare(strict_types=1);

namespace App\Enums;

enum MoneyboxTransactionTypeEnum: string
{
    case IN = 'in';
    case OUT = 'out';
    case TRANSFER = 'transfer';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (MoneyboxTransactionTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::IN => 'In',
            self::OUT => 'Out',
            self::TRANSFER => 'Transfer',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN => 'green',
            self::OUT => 'red',
            self::TRANSFER => 'blue',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::IN => 'arrow-down',
            self::OUT => 'arrow-up',
            self::TRANSFER => 'arrow-right-left',
        };
    }
}
