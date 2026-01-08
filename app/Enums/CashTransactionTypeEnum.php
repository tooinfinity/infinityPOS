<?php

declare(strict_types=1);

namespace App\Enums;

enum CashTransactionTypeEnum: string
{
    case SALE = 'sale';
    case EXPENSE = 'expense';
    case WITHDRAWAL = 'withdrawal';
    case DEPOSIT = 'deposit';
    case OPENING = 'opening';
    case CLOSING = 'closing';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::SALE => 'Sale',
            self::EXPENSE => 'Expense',
            self::WITHDRAWAL => 'Withdrawal',
            self::DEPOSIT => 'Deposit',
            self::OPENING => 'Opening Balance',
            self::CLOSING => 'Closing Balance',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SALE => 'green',
            self::EXPENSE => 'red',
            self::WITHDRAWAL => 'orange',
            self::DEPOSIT => 'blue',
            self::OPENING => 'purple',
            self::CLOSING => 'yellow',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SALE => 'trending-up',
            self::EXPENSE => 'trending-down',
            self::WITHDRAWAL => 'arrow-down-circle',
            self::DEPOSIT => 'arrow-up-circle',
            self::OPENING => 'unlock',
            self::CLOSING => 'lock',
        };
    }

    /**
     * Is this a positive cash flow?
     */
    public function isInflow(): bool
    {
        return in_array($this, [self::SALE, self::DEPOSIT, self::OPENING]);
    }

    /**
     * Is this a negative cash flow?
     */
    public function isOutflow(): bool
    {
        return in_array($this, [self::EXPENSE, self::WITHDRAWAL]);
    }
}
