<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionEnum: string
{
    // Dashboard
    case VIEW_DASHBOARD = 'view_dashboard';

    // Products
    case VIEW_PRODUCTS = 'view_products';
    case CREATE_PRODUCTS = 'create_products';
    case EDIT_PRODUCTS = 'edit_products';
    case DELETE_PRODUCTS = 'delete_products';
    case IMPORT_PRODUCTS = 'import_products';
    case EXPORT_PRODUCTS = 'export_products';

    // Categories
    case VIEW_CATEGORIES = 'view_categories';
    case CREATE_CATEGORIES = 'create_categories';
    case EDIT_CATEGORIES = 'edit_categories';
    case DELETE_CATEGORIES = 'delete_categories';

    // Brands
    case VIEW_BRANDS = 'view_brands';
    case CREATE_BRANDS = 'create_brands';
    case EDIT_BRANDS = 'edit_brands';
    case DELETE_BRANDS = 'delete_brands';

    // Units
    case VIEW_UNITS = 'view_units';
    case CREATE_UNITS = 'create_units';
    case EDIT_UNITS = 'edit_units';
    case DELETE_UNITS = 'delete_units';

    // Taxes
    case VIEW_TAXES = 'view_taxes';
    case CREATE_TAXES = 'create_taxes';
    case EDIT_TAXES = 'edit_taxes';
    case DELETE_TAXES = 'delete_taxes';

    // Sales
    case VIEW_SALES = 'view_sales';
    case CREATE_SALES = 'create_sales';
    case EDIT_SALES = 'edit_sales';
    case DELETE_SALES = 'delete_sales';
    case EXPORT_SALES = 'export_sales';

    // Purchases
    case VIEW_PURCHASES = 'view_purchases';
    case CREATE_PURCHASES = 'create_purchases';
    case EDIT_PURCHASES = 'edit_purchases';
    case DELETE_PURCHASES = 'delete_purchases';
    case EXPORT_PURCHASES = 'export_purchases';

    // Expenses
    case VIEW_EXPENSES = 'view_expenses';
    case CREATE_EXPENSES = 'create_expenses';
    case EDIT_EXPENSES = 'edit_expenses';
    case DELETE_EXPENSES = 'delete_expenses';

    // Moneyboxes
    case VIEW_MONEYBOXES = 'view_moneyboxes';
    case CREATE_MONEYBOXES = 'create_moneyboxes';
    case EDIT_MONEYBOXES = 'edit_moneyboxes';
    case DELETE_MONEYBOXES = 'delete_moneyboxes';

    // Contacts
    case VIEW_CONTACTS = 'view_contacts';
    case CREATE_CONTACTS = 'create_contacts';
    case EDIT_CONTACTS = 'edit_contacts';
    case DELETE_CONTACTS = 'delete_contacts';
    case IMPORT_CONTACTS = 'import_contacts';
    case EXPORT_CONTACTS = 'export_contacts';

    // Warehouses
    case VIEW_WAREHOUSES = 'view_warehouses';
    case CREATE_WAREHOUSES = 'create_warehouses';
    case EDIT_WAREHOUSES = 'edit_warehouses';
    case DELETE_WAREHOUSES = 'delete_warehouses';

    // Reports
    case VIEW_REPORTS = 'view_reports';
    case EXPORT_REPORTS = 'export_reports';

    // POS
    case ACCESS_POS = 'access_pos';
    case PROCESS_SALES = 'process_sales';
    case APPLY_DISCOUNTS = 'apply_discounts';
    case VOID_SALES = 'void_sales';

    // Settings (Admin only)
    case VIEW_SETTINGS = 'view_settings';
    case EDIT_SETTINGS = 'edit_settings';

    // Users Management (Admin only)
    case VIEW_USERS = 'view_users';
    case CREATE_USERS = 'create_users';
    case EDIT_USERS = 'edit_users';
    case DELETE_USERS = 'delete_users';

    // Roles Management (Admin only)
    case VIEW_ROLES = 'view_roles';
    case CREATE_ROLES = 'create_roles';
    case EDIT_ROLES = 'edit_roles';
    case DELETE_ROLES = 'delete_roles';

    /**
     * Get permissions for a specific role.
     *
     * @return array<int, string>
     */
    public static function forRole(RoleEnum $role): array
    {
        return match ($role) {
            RoleEnum::ADMIN => self::allPermissions(),
            RoleEnum::MANAGER => self::managerPermissions(),
            RoleEnum::CASHIER => self::cashierPermissions(),
        };
    }

    /**
     * Get all permissions as an array.
     *
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Permissions required for manager role.
     *
     * @return array<int, string>
     */
    public static function managerPermissions(): array
    {
        $excluded = [
            self::VIEW_SETTINGS,
            self::EDIT_SETTINGS,
            self::VIEW_USERS,
            self::CREATE_USERS,
            self::EDIT_USERS,
            self::DELETE_USERS,
            self::VIEW_ROLES,
            self::CREATE_ROLES,
            self::EDIT_ROLES,
            self::DELETE_ROLES,
        ];

        return array_values(
            array_diff(
                self::allPermissions(),
                array_map(fn (PermissionEnum $case) => $case->value, $excluded)
            )
        );
    }

    /**
     * Permissions required for cashier role.
     *
     * @return array<int, string>
     */
    public static function cashierPermissions(): array
    {
        return [
            self::VIEW_DASHBOARD->value,
            self::ACCESS_POS->value,
            self::PROCESS_SALES->value,
            self::APPLY_DISCOUNTS->value,
            self::VIEW_SALES->value,
            self::VIEW_PRODUCTS->value,
            self::VIEW_CONTACTS->value,
        ];
    }

    /**
     * Group permissions by module.
     *
     * @return array<string, array{label: string, permissions: array<array{value: string, label: string}>}>
     */
    public static function groupedByModule(): array
    {
        $grouped = [];

        foreach (self::cases() as $permission) {
            $module = $permission->module()->value;

            if (! isset($grouped[$module])) {
                $grouped[$module] = [
                    'label' => $permission->module()->label(),
                    'permissions' => [],
                ];
            }

            $grouped[$module]['permissions'][] = [
                'value' => $permission->value,
                'label' => $permission->label(),
            ];
        }

        return $grouped;
    }

    public function module(): ModuleEnum
    {
        return match ($this) {
            self::VIEW_DASHBOARD => ModuleEnum::DASHBOARD,

            self::VIEW_PRODUCTS, self::CREATE_PRODUCTS, self::EDIT_PRODUCTS,
            self::DELETE_PRODUCTS, self::IMPORT_PRODUCTS, self::EXPORT_PRODUCTS => ModuleEnum::PRODUCTS,

            self::VIEW_CATEGORIES, self::CREATE_CATEGORIES, self::EDIT_CATEGORIES,
            self::DELETE_CATEGORIES => ModuleEnum::CATEGORIES,

            self::VIEW_BRANDS, self::CREATE_BRANDS, self::EDIT_BRANDS,
            self::DELETE_BRANDS => ModuleEnum::BRANDS,

            self::VIEW_UNITS, self::CREATE_UNITS, self::EDIT_UNITS,
            self::DELETE_UNITS => ModuleEnum::UNITS,

            self::VIEW_TAXES, self::CREATE_TAXES, self::EDIT_TAXES,
            self::DELETE_TAXES => ModuleEnum::TAXES,

            self::VIEW_SALES, self::CREATE_SALES, self::EDIT_SALES,
            self::DELETE_SALES, self::EXPORT_SALES => ModuleEnum::SALES,

            self::VIEW_PURCHASES, self::CREATE_PURCHASES, self::EDIT_PURCHASES,
            self::DELETE_PURCHASES, self::EXPORT_PURCHASES => ModuleEnum::PURCHASES,

            self::VIEW_EXPENSES, self::CREATE_EXPENSES, self::EDIT_EXPENSES,
            self::DELETE_EXPENSES => ModuleEnum::EXPENSES,

            self::VIEW_MONEYBOXES, self::CREATE_MONEYBOXES, self::EDIT_MONEYBOXES,
            self::DELETE_MONEYBOXES => ModuleEnum::MONEYBOXES,

            self::VIEW_CONTACTS, self::CREATE_CONTACTS, self::EDIT_CONTACTS,
            self::DELETE_CONTACTS, self::IMPORT_CONTACTS, self::EXPORT_CONTACTS => ModuleEnum::CONTACTS,

            self::VIEW_WAREHOUSES, self::CREATE_WAREHOUSES, self::EDIT_WAREHOUSES,
            self::DELETE_WAREHOUSES => ModuleEnum::WAREHOUSES,

            self::VIEW_REPORTS, self::EXPORT_REPORTS => ModuleEnum::REPORTS,

            self::ACCESS_POS, self::PROCESS_SALES, self::APPLY_DISCOUNTS,
            self::VOID_SALES => ModuleEnum::POS,

            self::VIEW_SETTINGS, self::EDIT_SETTINGS => ModuleEnum::SETTINGS,

            self::VIEW_USERS, self::CREATE_USERS, self::EDIT_USERS,
            self::DELETE_USERS => ModuleEnum::USERS,

            self::VIEW_ROLES, self::CREATE_ROLES, self::EDIT_ROLES,
            self::DELETE_ROLES => ModuleEnum::ROLES,
        };
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
