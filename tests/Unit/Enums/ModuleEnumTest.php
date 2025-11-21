<?php

declare(strict_types=1);

use App\Enums\ModuleEnum;

it('has correct values', function (): void {
    expect(ModuleEnum::PRODUCTS->value)->toBe('products')
        ->and(ModuleEnum::POS->value)->toBe('pos')
        ->and(ModuleEnum::DASHBOARD->value)->toBe('dashboard');
});

it('returns correct labels', function (): void {
    expect(ModuleEnum::PRODUCTS->label())->toBe('Products')
        ->and(ModuleEnum::POS->label())->toBe('Point of Sale')
        ->and(ModuleEnum::MONEYBOXES->label())->toBe('Money Boxes');
});

it('returns POS modules', function (): void {
    $posModules = ModuleEnum::posModules();

    expect($posModules)->toBeArray()
        ->toContain(ModuleEnum::POS, ModuleEnum::SALES, ModuleEnum::DASHBOARD);
});

it('returns manager modules', function (): void {
    $managerModules = ModuleEnum::managerModules();

    expect($managerModules)->toBeArray()
        ->not->toContain(ModuleEnum::SETTINGS, ModuleEnum::USERS, ModuleEnum::ROLES);
});

it('returns all module values', function (): void {
    $values = ModuleEnum::values();

    expect($values)->toBeArray()
        ->toHaveCount(17)
        ->toContain('products', 'pos', 'dashboard');
});
