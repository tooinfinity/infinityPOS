<?php

declare(strict_types=1);

use App\Enums\RoleEnum;

it('has correct values', function (): void {
    expect(RoleEnum::ADMIN->value)->toBe('admin')
        ->and(RoleEnum::MANAGER->value)->toBe('manager')
        ->and(RoleEnum::CASHIER->value)->toBe('cashier');
});

it('returns correct labels', function (): void {
    expect(RoleEnum::ADMIN->label())->toBe('Administrator')
        ->and(RoleEnum::MANAGER->label())->toBe('Manager')
        ->and(RoleEnum::CASHIER->label())->toBe('Cashier');
});

it('returns correct descriptions', function (): void {
    expect(RoleEnum::ADMIN->description())
        ->toContain('Full system access')
        ->and(RoleEnum::MANAGER->description())
        ->toContain('Manage inventory')
        ->and(RoleEnum::CASHIER->description())
        ->toContain('POS');
});

it('returns all role values', function (): void {
    $cases = RoleEnum::cases();

    expect($cases)->toBeArray()
        ->toHaveCount(3);

});

it('returns array representation', function (): void {
    $array = RoleEnum::toArray();

    expect($array)->toBeArray()
        ->toHaveCount(3)
        ->and($array[0])->toHaveKeys(['value', 'label', 'description']);
});

it('can be instantiated from string', function (): void {
    $role = RoleEnum::from('admin');

    expect($role)->toBeInstanceOf(RoleEnum::class)
        ->and($role->value)->toBe('admin');
});
