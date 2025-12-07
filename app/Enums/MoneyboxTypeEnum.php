<?php

declare(strict_types=1);

namespace App\Enums;

enum MoneyboxTypeEnum: string
{
    case CASH_REGISTER = 'cash_register';
    case BANK_ACCOUNT = 'bank_account';
    case MOBILE_MONEY = 'mobile_money';
    case OTHER = 'other';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (MoneyboxTypeEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH_REGISTER => 'Cash Register',
            self::BANK_ACCOUNT => 'Bank Account',
            self::MOBILE_MONEY => 'Mobile Money',
            self::OTHER => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CASH_REGISTER => 'cash-register',
            self::BANK_ACCOUNT => 'building-library',
            self::MOBILE_MONEY => 'device-phone-mobile',
            self::OTHER => 'wallet',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CASH_REGISTER => 'Physical cash register or drawer',
            self::BANK_ACCOUNT => 'Bank account for transfers',
            self::MOBILE_MONEY => 'Mobile money service (M-Pesa, etc.)',
            self::OTHER => 'Other payment method',
        };
    }
}
