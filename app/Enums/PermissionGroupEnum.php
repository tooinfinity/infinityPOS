<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionGroupEnum: string
{
    case USERS = 'users';
    // TODO: add permissions groups
    // case POS = 'pos';
    // case INVENTORY = 'inventory';
    // case PRODUCTS = 'products';
    // case SUPPLIERS = 'suppliers';
    // case CUSTOMERS = 'customers';
    // case REPORTS = 'reports';
    // case SETTINGS = 'settings';

    public function label(): string
    {
        return match ($this) {
            self::USERS => 'Users',
            // add more groups here
        };
    }

    /**
     * @return array<int, PermissionEnum>
     */
    public function permissions(): array
    {
        $name = $this->name;

        return collect(PermissionEnum::cases())
            ->filter(fn (PermissionEnum $permission): bool => $permission->group()->name === $name
            )
            ->values()
            ->all();
    }
}
