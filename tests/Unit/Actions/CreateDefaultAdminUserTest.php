<?php

declare(strict_types=1);

use App\Actions\CreateDefaultAdminUser;
use App\Actions\CreateRoles;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

it('creates a default admin user', function (): void {
    (new CreateRoles)->handle();

    $action = new CreateDefaultAdminUser;

    $user = $action->handle(
        'Admin User',
        'admin@example.com',
        'password123'
    );

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Admin User')
        ->and($user->email)->toBe('admin@example.com')
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue()
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

it('returns existing user if email already exists', function (): void {
    (new CreateRoles)->handle();

    $existingUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    $action = new CreateDefaultAdminUser;

    $user = $action->handle(
        'New Admin Name',
        'admin@example.com',
        'newpassword123'
    );

    expect($user->id)->toBe($existingUser->id)
        ->and($user->name)->toBe($existingUser->name)
        ->and($user->email)->toBe('admin@example.com');
});

it('validates name is required', function (): void {
    (new CreateRoles)->handle();

    $action = new CreateDefaultAdminUser;

    expect(fn (): User => $action->handle('', 'admin@example.com', 'password123'))
        ->toThrow(ValidationException::class);
});

it('validates email is required', function (): void {
    (new CreateRoles)->handle();

    $action = new CreateDefaultAdminUser;

    expect(fn (): User => $action->handle('Admin User', '', 'password123'))
        ->toThrow(ValidationException::class);
});

it('validates email format', function (): void {
    (new CreateRoles)->handle();

    $action = new CreateDefaultAdminUser;

    expect(fn (): User => $action->handle('Admin User', 'invalid-email', 'password123'))
        ->toThrow(ValidationException::class);
});

it('validates password is required', function (): void {
    (new CreateRoles)->handle();

    $action = new CreateDefaultAdminUser;

    expect(fn (): User => $action->handle('Admin User', 'admin@example.com', ''))
        ->toThrow(ValidationException::class);
});

it('validates password minimum length', function (): void {
    (new CreateRoles)->handle();

    $action = new CreateDefaultAdminUser;

    expect(fn (): User => $action->handle('Admin User', 'admin@example.com', 'short'))
        ->toThrow(ValidationException::class);
});
