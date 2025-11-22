<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (RoleEnum $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::CASHIER => 'Cashier',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full system access with all permissions',
            self::MANAGER => 'Manage inventory, sales, and operations',
            self::CASHIER => 'Access POS and process sales only',
        };
    }
}
