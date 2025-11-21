<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\AssignPermissionsToRoles;
use App\Actions\CreateDefaultAdminUser;
use App\Actions\SyncPermissions;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

final class AppSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup
                            {--fresh : Delete all existing roles and permissions before setup}
                            {--skip-user : Skip user creation step}
                            {--admin-email= : Email for admin user}
                            {--admin-password= : Password for admin user}
                            {--admin-name= : Name for admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create initial admin user, roles, and permissions';

    /**
     * @throws Throwable
     */
    public function handle(
        SyncPermissions $syncPermissions,
        AssignPermissionsToRoles $assignPermissions,
        CreateDefaultAdminUser $createAdmin
    ): int {
        $this->info('🚀 Starting APP Permission Setup...');
        $this->newLine();

        if ($this->option('fresh')) {
            if ($this->confirm('⚠️  This will delete all existing roles and permissions. Continue?', false)) {
                $this->cleanupExisting();
            } else {
                $this->warn('Setup cancelled.');

                return self::FAILURE;
            }
        }

        // Step 1: Sync Permissions
        $this->info('📝 Step 1: Syncing permissions...');
        $permissionResults = $syncPermissions->handle();

        $this->line('   ✓ Created: '.count($permissionResults['created']));
        $this->line('   ✓ Existing: '.count($permissionResults['existing']));
        $this->line('   ✓ Deleted: '.$permissionResults['deleted']);
        $this->newLine();

        // Step 2: Create Roles
        $this->info('👥 Step 2: Creating roles...');
        $this->createRoles();
        $this->newLine();

        // Step 3: Assign Permissions to Roles
        $this->info('🔐 Step 3: Assigning permissions to roles...');
        $assignPermissions->handle();
        $this->displayRolePermissions();
        $this->newLine();

        // Step 4: Create User
        if (! $this->option('skip-user')) {
            $this->info('👤 Step 4: Creating admin user...');
            $this->createUser($createAdmin);
            $this->newLine();
        } else {
            $this->warn('⏭️  Step 4: User creation skipped');
            $this->newLine();
        }

        // Step 5: Clear Cache
        $this->info('🧹 Step 5: Clearing permission cache...');
        $this->call('permission:cache-reset');
        $this->newLine();

        $this->info('✅ Permission setup completed successfully!');
        $this->newLine();

        $this->displaySummary();

        return self::SUCCESS;
    }

    private function createUser(CreateDefaultAdminUser $createAdmin, int $maxAttempts = 3): void
    {
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            $attempt++;
            if ($this->option('admin-email') && $this->option('admin-password') && $this->option('admin-name')) {
                /** @var string $name */
                $name = $this->option('admin-name');
                /** @var string $email */
                $email = $this->option('admin-email');
                /** @var string $password */
                $password = $this->option('admin-password');
            } else {
                // Interactive mode
                $this->line('   Please provide admin user details:');
                $this->newLine();

                /** @var string $name */
                $name = $this->ask('   Name', 'System Administrator');
                /** @var string $email */
                $email = $this->ask('   Email', 'admin@example.com');
                /** @var string $password */
                $password = $this->secret('   Password');
                /** @var string $passwordConfirmation */
                $passwordConfirmation = $this->secret('   Confirm Password');

                if ($password !== $passwordConfirmation) {
                    $this->error('   ❌ Passwords do not match.');

                    if ($attempt < $maxAttempts && $this->confirm('   Would you like to try again?', true)) {
                        continue;
                    }

                    if ($attempt >= $maxAttempts) {
                        $this->error('   Maximum attempts reached. Aborting user creation.');

                        return;
                    }
                }
            }

            try {
                $admin = $createAdmin->handle($name, $email, $password);

                $this->newLine();
                $this->info('   ✅ Admin user created successfully!');
                $this->line('   ✓ Name: '.$admin->name);
                $this->line('   ✓ Email: '.$admin->email);
                $this->line('   ✓ Password: '.str_repeat('•', 8));
                $this->line('   ✓ Role: '.RoleEnum::ADMIN->value);

                return;
            } catch (Throwable $throwable) {
                $this->error('   ❌ Failed to create admin user: '.$throwable->getMessage());

                if ($attempt < $maxAttempts && $this->confirm('   Would you like to try again?', true)) {
                    continue;
                }
            }
        }

        $this->error('   Failed to create admin user after '.$maxAttempts.' attempts.');
    }

    private function cleanupExisting(): void
    {
        $this->warn('🗑️  Cleaning up existing roles and permissions...');

        Role::query()->delete();
        Permission::query()->delete();

        $this->info('   ✓ Cleanup completed');
        $this->newLine();
    }

    private function createRoles(): void
    {
        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::query()->firstOrCreate(
                ['name' => $roleEnum->value],
                ['guard_name' => 'web']
            );

            $status = $role->wasRecentlyCreated ? 'Created' : 'Exists';
            $this->line(sprintf('   ✓ %s: %s (%s)', $status, $roleEnum->label(), $roleEnum->description()));
        }
    }

    private function displayRolePermissions(): void
    {
        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::findByName($roleEnum->value);
            $permissionsCount = $role->permissions()->count();

            $this->line(sprintf('   ✓ %s: %d permissions', $roleEnum->value, $permissionsCount));
        }
    }

    private function displaySummary(): void
    {
        $this->info('📊 Setup Summary:');
        $this->table(
            ['Role', 'Permissions', 'Description'],
            collect(RoleEnum::cases())->map(function (RoleEnum $roleEnum): array {
                $role = Role::findByName($roleEnum->value);

                return [
                    $roleEnum->value,
                    $role->permissions()->count(),
                    $roleEnum->description(),
                ];
            })->all()
        );

        $this->newLine();
        $this->info('💡 Next Steps:');
        $this->line('   • Login with the admin credentials');
        $this->line('   • Update the admin password if using default');
        $this->line('   • Create additional users via the admin panel');
        $this->newLine();

        $adminCount = User::query()->whereHas('roles', function (Builder $query): void {
            $query->where('name', RoleEnum::ADMIN->value);
        })->count();
        $this->line('   Total admin users: '.$adminCount);
    }
}
