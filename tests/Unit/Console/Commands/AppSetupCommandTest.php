<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('runs setup command successfully', function (): void {
    $this->artisan('app:setup', [
        '--skip-user' => true,
    ])->assertSuccessful();

    expect(Role::query()->count())->toBe(3)
        ->and(Permission::query()->count())->toBeGreaterThan(0);
});

it('creates admin user when not skipping', function (): void {
    $this->artisan('app:setup', [
        '--admin-name' => 'Test Admin',
        '--admin-email' => 'admin@test.com',
        '--admin-password' => 'P@ssword123!',
    ])->assertSuccessful();

    $user = User::query()->where('email', 'admin@test.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test Admin')
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
});

it('creates admin user with interactive prompts', function (): void {
    $this->artisan('app:setup')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'Test Admin')
        ->expectsQuestion('   Email', 'admin@example.com')
        ->expectsQuestion('   Password', 'P@ssword123!')
        ->expectsQuestion('   Confirm Password', 'P@ssword123!')
        ->assertSuccessful();

    $user = User::query()->where('email', 'admin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test Admin')
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
});

it('handles password mismatch with retry in interactive mode', function (): void {
    $this->artisan('app:setup')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'Test Admin')
        ->expectsQuestion('   Email', 'retry@example.com')
        ->expectsQuestion('   Password', 'P@ssword123!')
        ->expectsQuestion('   Confirm Password', 'different')
        ->expectsOutput('   ❌ Validation failed:')
        ->expectsConfirmation('   Would you like to try again?', 'yes')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'Test Admin')
        ->expectsQuestion('   Email', 'retry@example.com')
        ->expectsQuestion('   Password', 'P@ssword123!')
        ->expectsQuestion('   Confirm Password', 'P@ssword123!')
        ->assertSuccessful();

    $user = User::query()->where('email', 'retry@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
});

it('handles password mismatch without retry', function (): void {
    $this->artisan('app:setup')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'Default Admin')
        ->expectsQuestion('   Email', 'default@example.com')
        ->expectsQuestion('   Password', 'P@ssword123!')
        ->expectsQuestion('   Confirm Password', 'different')
        ->expectsOutput('   ❌ Validation failed:')
        ->expectsConfirmation('   Would you like to try again?', 'no')
        ->assertSuccessful();

    $user = User::query()->where('email', 'default@example.com')->first();

    expect($user)->toBeNull();
});

it('skips user creation when skip-user option is provided', function (): void {
    $this->artisan('app:setup', [
        '--skip-user' => true,
    ])->assertSuccessful();

    expect(User::query()->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::ADMIN->value))->count())->toBe(0);
});

it('cleans up existing roles and permissions when fresh option is used', function (): void {
    Role::query()->create(['name' => 'old_role', 'guard_name' => 'web']);
    Permission::query()->create(['name' => 'old_permission', 'guard_name' => 'web']);

    $this->artisan('app:setup', [
        '--fresh' => true,
        '--skip-user' => true,
    ])
        ->expectsConfirmation('⚠️  This will delete all existing roles and permissions. Continue?', 'yes')
        ->assertSuccessful();

    expect(Role::query()->where('name', 'old_role')->exists())->toBeFalse()
        ->and(Permission::query()->where('name', 'old_permission')->exists())->toBeFalse();
});

it('cancels setup when fresh confirmation is denied', function (): void {
    $this->artisan('app:setup', [
        '--fresh' => true,
        '--skip-user' => true,
    ])
        ->expectsConfirmation('⚠️  This will delete all existing roles and permissions. Continue?', 'no')
        ->assertFailed();
});

it('syncs permissions during setup', function (): void {
    $this->artisan('app:setup', [
        '--skip-user' => true,
    ])->assertSuccessful();

    $permissionCount = Permission::query()->count();
    expect($permissionCount)->toBeGreaterThan(0);

    // Run again to ensure it handles existing permissions
    $this->artisan('app:setup', [
        '--skip-user' => true,
    ])->assertSuccessful();

    expect(Permission::query()->count())->toBe($permissionCount);
});

it('creates all roles during setup', function (): void {
    $this->artisan('app:setup', [
        '--skip-user' => true,
    ])->assertSuccessful();

    foreach (RoleEnum::cases() as $roleEnum) {
        $role = Role::findByName($roleEnum->value);
        expect($role)->not->toBeNull()
            ->and($role->name)->toBe($roleEnum->value);
    }
});

it('assigns permissions to roles during setup', function (): void {
    $this->artisan('app:setup', [
        '--skip-user' => true,
    ])->assertSuccessful();

    foreach (RoleEnum::cases() as $roleEnum) {
        $role = Role::findByName($roleEnum->value);
        expect($role->permissions()->count())->toBeGreaterThan(0);
    }
});

it('handles command with all options', function (): void {
    $this->artisan('app:setup', [
        '--fresh' => true,
        '--admin-name' => 'Full Admin',
        '--admin-email' => 'fulladmin@test.com',
        '--admin-password' => 'P@ssword123!',
    ])
        ->expectsConfirmation('⚠️  This will delete all existing roles and permissions. Continue?', 'yes')
        ->assertSuccessful();

    $user = User::query()->where('email', 'fulladmin@test.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
});

it('handles user creation failure with retry', function (): void {
    $this->artisan('app:setup')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'Error Admin')
        ->expectsQuestion('   Email', 'error@example.com')
        ->expectsQuestion('   Password', 'short')
        ->expectsQuestion('   Confirm Password', 'short')
        ->expectsOutput('   ❌ Validation failed:')
        ->expectsConfirmation('   Would you like to try again?', 'yes')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'Error Admin')
        ->expectsQuestion('   Email', 'error@example.com')
        ->expectsQuestion('   Password', 'P@ssword123!')
        ->expectsQuestion('   Confirm Password', 'P@ssword123!')
        ->assertSuccessful();

    $user = User::query()->where('email', 'error@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
});

it('handles user creation failure without retry', function (): void {
    $this->artisan('app:setup')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'No Retry Admin')
        ->expectsQuestion('   Email', 'noretry@example.com')
        ->expectsQuestion('   Password', 'short')
        ->expectsQuestion('   Confirm Password', 'short')
        ->expectsOutput('   ❌ Validation failed:')
        ->expectsConfirmation('   Would you like to try again?', 'no')
        ->assertSuccessful();

    $user = User::query()->where('email', 'noretry@example.com')->first();
    expect($user)->toBeNull();
});

it('failed to create admin user with throwable exception that can try', function (): void {
    User::creating(function (): void {
        throw new RuntimeException('Simulated database error');
    });

    $this->artisan('app:setup')
        ->expectsOutput('   Please provide admin user details:')
        ->expectsQuestion('   Name', 'Fail Admin')
        ->expectsQuestion('   Email', 'fail@example.com')
        ->expectsQuestion('   Password', 'P@ssword123!')
        ->expectsQuestion('   Confirm Password', 'P@ssword123!')
        ->expectsOutput('   ❌ Failed to create admin user: Simulated database error')
        ->expectsConfirmation('   Would you like to try again?', 'no')
        ->assertSuccessful();

    expect(User::query()->where('email', 'fail@example.com')->first())->toBeNull();
});
