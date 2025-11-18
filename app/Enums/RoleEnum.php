<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';

    /**
     * @return array<string, string>
     */
    public static function toSelectArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
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
            self::ADMIN => 'Administrator - Full system access',
            self::MANAGER => 'Manager - Manage operations and reports',
            self::CASHIER => 'Cashier - POS operations only',
        };
    }

    /**
     * @return array<int, PermissionEnum>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::ADMIN => PermissionEnum::cases(),
            self::MANAGER => [
                PermissionEnum::VIEW_USER,
                PermissionEnum::CREATE_USER,
                PermissionEnum::UPDATE_USER,
            ],
            self::CASHIER => [
                PermissionEnum::VIEW_USER,
            ],
        };
    }
}
