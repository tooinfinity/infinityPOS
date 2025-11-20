<?php

declare(strict_types=1);

namespace App\Enums;

enum ModuleEnum: string
{
    case DASHBOARD = 'dashboard';
    case PRODUCTS = 'products';
    case CATEGORIES = 'categories';
    case BRANDS = 'brands';
    case UNITS = 'units';
    case TAXES = 'taxes';
    case SALES = 'sales';
    case PURCHASES = 'purchases';
    case EXPENSES = 'expenses';
    case MONEYBOXES = 'moneyboxes';
    case CONTACTS = 'contacts';
    case WAREHOUSES = 'warehouses';
    case SETTINGS = 'settings';
    case POS = 'pos';
    case USERS = 'users';
    case ROLES = 'roles';
    case REPORTS = 'reports';

    /**
     * Get all POS modules.
     *
     * @return array<int, self>
     */
    public static function posModules(): array
    {
        return [
            self::POS,
            self::SALES,
            self::DASHBOARD,
        ];
    }

    /**
     * Get all manager modules.
     *
     * @return array<int, self>
     */
    public static function managerModules(): array
    {
        return array_filter(
            self::cases(),
            fn (ModuleEnum $module): bool => ! in_array($module, [self::SETTINGS, self::USERS, self::ROLES])
        );
    }

    /**
     * Get all module values as an array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the module label.
     */
    public function label(): string
    {
        return match ($this) {
            self::DASHBOARD => 'Dashboard',
            self::PRODUCTS => 'Products',
            self::CATEGORIES => 'Categories',
            self::BRANDS => 'Brands',
            self::UNITS => 'Units',
            self::TAXES => 'Taxes',
            self::SALES => 'Sales',
            self::PURCHASES => 'Purchases',
            self::EXPENSES => 'Expenses',
            self::MONEYBOXES => 'Money Boxes',
            self::CONTACTS => 'Contacts',
            self::WAREHOUSES => 'Warehouses',
            self::SETTINGS => 'Settings',
            self::POS => 'Point of Sale',
            self::USERS => 'Users',
            self::ROLES => 'Roles & Permissions',
            self::REPORTS => 'Reports',
        };
    }
}
