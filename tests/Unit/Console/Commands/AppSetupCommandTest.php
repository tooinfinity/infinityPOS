<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function expectAdminUser(string $email, string $name): void
{
    $user = User::query()->where('email', $email)->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe($name)
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
}

function expectNoAdminUser(string $email): void
{
    expect(User::query()->where('email', $email)->first())->toBeNull();
}

describe('basic setup', function (): void {
    it('runs setup command successfully', function (): void {
        $this->artisan('app:setup', ['--skip-user' => true])
            ->assertSuccessful();

        expect(Role::query()->count())->toBe(3)
            ->and(Permission::query()->count())->toBeGreaterThan(0);
    });

    it('creates all roles during setup', function (): void {
        $this->artisan('app:setup', ['--skip-user' => true])
            ->assertSuccessful();

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::findByName($roleEnum->value);
            expect($role)->not->toBeNull()
                ->and($role->name)->toBe($roleEnum->value)
                ->and($role->permissions()->count())->toBeGreaterThan(0);
        }
    });

    it('syncs permissions during setup', function (): void {
        $this->artisan('app:setup', ['--skip-user' => true])
            ->assertSuccessful();

        $permissionCount = Permission::query()->count();
        expect($permissionCount)->toBeGreaterThan(0);

        // Run again to ensure idempotency
        $this->artisan('app:setup', ['--skip-user' => true])
            ->assertSuccessful();

        expect(Permission::query()->count())->toBe($permissionCount);
    });

    it('removes obsolete permissions and shows warning', function (): void {
        Permission::create(['name' => 'old_obsolete_permission', 'guard_name' => 'web']);

        $this->artisan('app:setup', ['--skip-user' => true])
            ->expectsOutput('   ⚠️  Deleted 1 obsolete permission(s)')
            ->assertSuccessful();

        expect(Permission::query()->where('name', 'old_obsolete_permission')->exists())
            ->toBeFalse();
    });
});

describe('admin user creation', function (): void {
    it('creates admin user with command options', function (): void {
        $this->artisan('app:setup', [
            '--admin-name' => 'Test Admin',
            '--admin-email' => 'admin@test.com',
            '--admin-password' => 'P@ssword123!',
        ])->assertSuccessful();

        expectAdminUser('admin@test.com', 'Test Admin');
    });

    it('creates admin user with interactive prompts', function (): void {
        $this->artisan('app:setup')
            ->expectsOutput('   Please provide admin user details:')
            ->expectsQuestion('   Name', 'Test Admin')
            ->expectsQuestion('   Email', 'admin@example.com')
            ->expectsQuestion('   Password', 'P@ssword123!')
            ->expectsQuestion('   Confirm Password', 'P@ssword123!')
            ->assertSuccessful();

        expectAdminUser('admin@example.com', 'Test Admin');
    });

    it('skips user creation when skip-user option is provided', function (): void {
        $this->artisan('app:setup', ['--skip-user' => true])
            ->assertSuccessful();

        expect(User::query()->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::ADMIN->value)
        )->count())->toBe(0);
    });
});

describe('password validation', function (): void {
    it('handles password mismatch with retry', function (): void {
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

        expectAdminUser('retry@example.com', 'Test Admin');
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

        expectNoAdminUser('default@example.com');
    });

    it('handles weak password validation with retry', function (): void {
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

        expectAdminUser('error@example.com', 'Error Admin');
    });

    it('handles weak password validation without retry', function (): void {
        $this->artisan('app:setup')
            ->expectsOutput('   Please provide admin user details:')
            ->expectsQuestion('   Name', 'No Retry Admin')
            ->expectsQuestion('   Email', 'noretry@example.com')
            ->expectsQuestion('   Password', 'short')
            ->expectsQuestion('   Confirm Password', 'short')
            ->expectsOutput('   ❌ Validation failed:')
            ->expectsConfirmation('   Would you like to try again?', 'no')
            ->assertSuccessful();

        expectNoAdminUser('noretry@example.com');
    });
});

describe('error handling', function (): void {
    it('handles user creation failure with exception', function (): void {
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

        expectNoAdminUser('fail@example.com');
    });
});

describe('fresh setup', function (): void {
    it('cleans up existing roles and permissions when fresh option is used', function (): void {
        Role::query()->create(['name' => 'old_role', 'guard_name' => 'web']);
        Permission::query()->create(['name' => 'old_permission', 'guard_name' => 'web']);

        $this->artisan('app:setup', ['--fresh' => true, '--skip-user' => true])
            ->expectsConfirmation('⚠️  This will delete all existing roles, permissions and users. Continue?', 'yes')
            ->assertSuccessful();

        expect(Role::query()->where('name', 'old_role')->exists())->toBeFalse()
            ->and(Permission::query()->where('name', 'old_permission')->exists())->toBeFalse();
    });

    it('cancels setup when fresh confirmation is denied', function (): void {
        $this->artisan('app:setup', ['--fresh' => true, '--skip-user' => true])
            ->expectsConfirmation('⚠️  This will delete all existing roles, permissions and users. Continue?', 'no')
            ->assertFailed();
    });

    it('handles command with all options', function (): void {
        $this->artisan('app:setup', [
            '--fresh' => true,
            '--admin-name' => 'Full Admin',
            '--admin-email' => 'fulladmin@test.com',
            '--admin-password' => 'P@ssword123!',
        ])
            ->expectsConfirmation('⚠️  This will delete all existing roles, permissions and users. Continue?', 'yes')
            ->assertSuccessful();

        expectAdminUser('fulladmin@test.com', 'Full Admin');
    });

    it('prevents running fresh command in production', function (): void {
        $this->app['env'] = 'production';

        $this->artisan('app:setup', ['--fresh' => true, '--skip-user' => true])
            ->expectsOutput('⚠️ ⚠️ ⚠️  ERROR: Cannot run fresh setup in production ⚠️ ⚠️ ⚠️')
            ->assertFailed();
    });
});
