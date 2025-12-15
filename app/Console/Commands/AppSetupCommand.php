<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateUser;
use App\Data\CreateSetupData;
use App\Data\CreateUserData;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

final class AppSetupCommand extends Command
{
    protected $signature = 'app:setup
                            {--fresh : Delete all existing roles and permissions (development only)}
                            {--skip-user : Skip admin user creation}
                            {--admin-name= : Admin user name}
                            {--admin-email= : Admin user email}
                            {--admin-password= : Admin user password}';

    protected $description = 'Setup roles, permissions, and create initial admin user';

    public function handle(CreateUser $createUser): int
    {
        $this->showHeader();

        if ($this->shouldRunFresh() && ! $this->handleFreshSetup()) {
            return self::FAILURE;
        }

        $this->syncPermissions();
        $this->createRoles();
        $this->assignPermissionsToRoles();

        if (! $this->option('skip-user')) {
            $this->createAdminUser($createUser);
        } else {
            $this->warn('⏭️  Step 4: User creation skipped');
        }

        $this->seedSettings();
        $this->clearPermissionCache();
        $this->showSummary();

        return self::SUCCESS;
    }

    private function showHeader(): void
    {
        $this->info('🚀 Starting APP Permission Setup...');
        $this->newLine();
    }

    private function shouldRunFresh(): bool
    {
        return (bool) $this->option('fresh');
    }

    private function handleFreshSetup(): bool
    {
        if (app()->environment('production')) {
            $this->warn('⚠️ ⚠️ ⚠️  ERROR: Cannot run fresh setup in production ⚠️ ⚠️ ⚠️');

            return false;
        }

        $this->showCleanupStats();

        if (! $this->confirm('⚠️  This will delete all existing roles, permissions and users. Continue?', false)) {
            $this->warn('Setup cancelled.');

            return false;
        }

        $this->cleanupExisting();

        return true;
    }

    private function showCleanupStats(): void
    {
        $roleCount = Role::query()->count();
        $permissionCount = Permission::query()->count();
        $userCount = User::query()->count();

        $this->line(sprintf(
            '   This will delete %d roles, %d permissions and %d users.',
            $roleCount,
            $permissionCount,
            $userCount
        ));
    }

    private function cleanupExisting(): void
    {
        $this->warn('🗑️  Cleaning up existing data...');

        DB::transaction(function (): void {
            DB::table('model_has_roles')->delete();
            DB::table('model_has_permissions')->delete();
            Role::query()->delete();
            Permission::query()->delete();
            User::query()->delete();
        });

        $this->info('   ✓ Cleanup completed');
        $this->newLine();
    }

    private function syncPermissions(): void
    {
        $this->info('📝 Step 1: Syncing permissions...');

        $results = DB::transaction(function (): array {
            $created = [];
            $existing = [];

            foreach (PermissionEnum::cases() as $permission) {
                $model = Permission::query()->firstOrCreate([
                    'name' => $permission->value,
                    'guard_name' => 'web',
                ]);

                $model->wasRecentlyCreated ? $created[] = $permission->value : $existing[] = $permission->value;
            }

            $deleted = $this->deleteObsoletePermissions();

            return [
                'created' => $created,
                'existing' => $existing,
                'deleted' => $deleted,
            ];
        });

        $this->showPermissionStats($results);
    }

    private function deleteObsoletePermissions(): int
    {
        $validPermissions = array_map(
            fn (PermissionEnum $case): string => $case->value,
            PermissionEnum::cases()
        );

        /** @var int $deleted */
        $deleted = Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $validPermissions)
            ->delete();

        if ($deleted > 0) {
            $this->warn(sprintf('   ⚠️  Deleted %d obsolete permission(s)', $deleted));
        }

        return $deleted;
    }

    /**
     * @param  array{created: list<string>, existing: list<string>, deleted: int}  $results
     */
    private function showPermissionStats(array $results): void
    {
        $this->line('   ✓ Created: '.count($results['created']));
        $this->line('   ✓ Existing: '.count($results['existing']));
        $this->line('   ✓ Deleted: '.$results['deleted']);
        $this->newLine();
    }

    private function createRoles(): void
    {
        $this->info('👥 Step 2: Creating roles...');

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleEnum->value,
                'guard_name' => 'web',
            ]);

            $status = $role->wasRecentlyCreated ? 'Created' : 'Exists';
            $this->line(sprintf('   ✓ %s: %s (%s)', $status, $roleEnum->label(), $roleEnum->description()));
        }

        $this->newLine();
    }

    private function assignPermissionsToRoles(): void
    {
        $this->info('🔐 Step 3: Assigning permissions to roles...');

        DB::transaction(function (): void {
            foreach (RoleEnum::cases() as $roleEnum) {
                $role = Role::findByName($roleEnum->value);
                $permissions = PermissionEnum::forRole($roleEnum);
                $role->syncPermissions($permissions);

                $this->line(sprintf('   ✓ %s: %d permissions', $roleEnum->value, count($permissions)));
            }
        });

        $this->newLine();
    }

    private function createAdminUser(CreateUser $createUser): void
    {
        $this->info('👤 Step 4: Creating admin user...');

        if ($this->hasAllAdminOptions()) {
            $this->createAdminFromOptions($createUser);

            return;
        }

        $this->createAdminInteractively($createUser);
    }

    private function hasAllAdminOptions(): bool
    {
        return $this->option('admin-name')
            && $this->option('admin-email')
            && $this->option('admin-password');
    }

    private function createAdminFromOptions(CreateUser $createUser): void
    {
        $name = $this->option('admin-name');
        $email = $this->option('admin-email');
        $password = $this->option('admin-password');

        assert(is_string($name));
        assert(is_string($email));
        assert(is_string($password));

        $this->processUserCreation($createUser, $name, $email, $password, $password);
    }

    private function createAdminInteractively(CreateUser $createUser): void
    {
        do {
            [$name, $email, $password, $confirmation] = $this->promptForCredentials();

            if ($this->processUserCreation($createUser, $name, $email, $password, $confirmation)) {
                break;
            }

            if (! $this->confirm('   Would you like to try again?', true)) {
                break;
            }
        } while (true);
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function promptForCredentials(): array
    {
        $this->line('   Please provide admin user details:');
        $this->newLine();

        $name = $this->ask('   Name', 'System Administrator');
        $email = $this->ask('   Email', 'admin@example.com');
        $password = $this->secret('   Password');
        $confirmation = $this->secret('   Confirm Password');

        assert(is_string($name));
        assert(is_string($email));
        assert(is_string($password));
        assert(is_string($confirmation));

        return [$name, $email, $password, $confirmation];
    }

    private function processUserCreation(
        CreateUser $createUser,
        string $name,
        string $email,
        string $password,
        string $passwordConfirmation
    ): bool {
        try {
            $setupData = CreateSetupData::validateAndCreate([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
                'role' => RoleEnum::ADMIN,
            ]);

            $userData = CreateUserData::from([
                'name' => $setupData->name,
                'email' => $setupData->email,
                'password' => $setupData->password,
                'role' => $setupData->role,
            ]);

            $admin = $this->createAndAssignRole($createUser, $userData);
            $this->showUserCreatedSuccess($admin);

            return true;
        } catch (ValidationException $e) {
            $this->showValidationErrors(array_values($e->validator->errors()->all()));

            return false;
        } catch (Throwable $throwable) {
            $this->error('   ❌ Failed to create admin user: '.$throwable->getMessage());

            return false;
        }
    }

    /**
     * @param  list<string>  $errors
     */
    private function showValidationErrors(array $errors): void
    {
        $this->error('   ❌ Validation failed:');
        foreach ($errors as $error) {
            $this->line('      - '.$error);
        }
    }

    private function createAndAssignRole(CreateUser $createUser, CreateUserData $data): User
    {
        $admin = $createUser->handle($data);
        $admin->assignRole(RoleEnum::ADMIN->value);

        return $admin;
    }

    private function showUserCreatedSuccess(User $admin): void
    {
        $this->newLine();
        $this->info('   ✅ Admin user created successfully!');
        $this->line('   ✓ Name: '.$admin->name);
        $this->line('   ✓ Email: '.$admin->email);
        $this->line('   ✓ Password: '.str_repeat('•', 8));
        $this->line('   ✓ Role: '.RoleEnum::ADMIN->value);
    }

    private function seedSettings(): void
    {
        $this->info('⚙️ Step 5: Setting up application settings...');

        $this->call('settings:seed', [
            '--force' => $this->shouldRunFresh(),
        ]);

        $this->newLine();
    }

    private function clearPermissionCache(): void
    {
        $this->newLine();
        $this->info('🧹 Step 6: Clearing permission cache...');
        $this->call('permission:cache-reset');
        $this->newLine();
    }

    private function showSummary(): void
    {
        $this->info('✅ Permission setup completed successfully!');
        $this->newLine();

        $this->showRoleSummaryTable();
        $this->showNextSteps();
        $this->showAdminCount();
    }

    private function showRoleSummaryTable(): void
    {
        $this->info('📊 Setup Summary:');

        $data = collect(RoleEnum::cases())->map(function (RoleEnum $roleEnum): array {
            $role = Role::findByName($roleEnum->value);

            return [
                $roleEnum->value,
                $role->permissions()->count(),
                $roleEnum->description(),
            ];
        });

        $this->table(['Role', 'Permissions', 'Description'], $data->all());
        $this->newLine();
    }

    private function showNextSteps(): void
    {
        $this->info('💡 Next Steps:');
        $this->line('   • Login with the admin credentials');
        $this->line('   • Update the admin password if using default');
        $this->line('   • Create additional users via the admin panel');
        $this->newLine();
    }

    private function showAdminCount(): void
    {
        $count = User::query()
            ->whereHas('roles', fn (Builder $q): Builder => $q->where('name', RoleEnum::ADMIN->value))
            ->count();

        $this->line('   Total admin users: '.$count);
    }
}
