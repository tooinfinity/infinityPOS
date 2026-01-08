<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case BANK_TRANSFER = 'bank_transfer';
    case CHECK = 'check';
    case SPLIT = 'split';

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
            fn (PaymentMethodEnum $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    /**
     * @return PaymentMethodEnum[]
     */
    public static function posOptions(): array
    {
        return [self::CASH, self::CARD, self::SPLIT];
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CHECK => 'Check',
            self::SPLIT => 'Split Payment',
        };
    }

    /**
     * Get icon name (for UI)
     */
    public function icon(): string
    {
        return match ($this) {
            self::CASH => 'banknote',
            self::CARD => 'credit-card',
            self::BANK_TRANSFER => 'building-2',
            self::CHECK => 'file-text',
            self::SPLIT => 'split',
        };
    }

    /**
     * Get color for UI badges
     */
    public function color(): string
    {
        return match ($this) {
            self::CASH => 'green',
            self::CARD => 'blue',
            self::BANK_TRANSFER => 'purple',
            self::CHECK => 'orange',
            self::SPLIT => 'yellow',
        };
    }

    /**
     * Check if method is cash-based
     */
    public function isCash(): bool
    {
        return $this === self::CASH;
    }
}
