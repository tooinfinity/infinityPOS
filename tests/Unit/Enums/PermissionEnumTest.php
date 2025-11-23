<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;

it('returns correct label', function (): void {
    expect(PermissionEnum::VIEW_PRODUCTS->label())->toBe('View Products')
        ->and(PermissionEnum::CREATE_PRODUCTS->label())->toBe('Create Products')
        ->and(PermissionEnum::DELETE_PRODUCTS->label())->toBe('Delete Products');
});

it('returns all permissions', function (): void {
    $permissions = PermissionEnum::allPermissions();

    expect($permissions)->toBeArray()
        ->not->toBeEmpty()
        ->toContain('view_products', 'create_products', 'access_pos');
});

it('returns correct admin permissions', function (): void {
    $permissions = PermissionEnum::forRole(RoleEnum::ADMIN);

    expect($permissions)->toBeArray()
        ->toContain('view_settings', 'edit_settings', 'view_users')
        ->and(count($permissions))->toBeGreaterThan(50);
});

it('returns correct manager permissions', function (): void {
    $permissions = PermissionEnum::forRole(RoleEnum::MANAGER);

    expect($permissions)->toBeArray()
        ->toContain('view_products', 'create_products', 'view_sales')
        ->not->toContain('view_settings', 'edit_settings', 'view_users');
});

it('returns correct cashier permissions', function (): void {
    $permissions = PermissionEnum::forRole(RoleEnum::CASHIER);

    expect($permissions)->toBeArray()
        ->toContain('access_pos', 'process_sales', 'view_dashboard')
        ->not->toContain('create_products', 'delete_products', 'view_settings')
        ->toHaveCount(7);
});
