<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateUser;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
                            {--admin-name= : Name for admin user}
                            {--admin-email= : Email for admin user}
                            {--admin-password= : Password for admin user}';

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
        CreateUser $createUser
    ): int {
        $this->info('🚀 Starting APP Permission Setup...');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('⚠️ ⚠️ ⚠️  DoNot run this command in production ⚠️ ⚠️ ⚠️ ');
            if ($this->confirm('⚠️  This will delete all existing roles and permissions. Continue?', false)) {
                $this->cleanupExisting();
            } else {
                $this->warn('Setup cancelled.');

                return self::FAILURE;
            }
        }

        // Step 1: Sync Permissions
        $this->info('📝 Step 1: Syncing permissions...');
        $permissionResults = $this->syncPermissions();

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
        $this->assignPermissionsToRoles();
        $this->displayRolePermissions();
        $this->newLine();

        // Step 4: Create User
        if (! $this->option('skip-user')) {
            $this->info('👤 Step 4: Creating admin user...');
            $this->createAdminUser($createUser);
        } else {
            $this->warn('⏭️  Step 4: User creation skipped');
        }

        $this->newLine();

        // Step 5: Clear Cache
        $this->info('🧹 Step 5: Clearing permission cache...');
        $this->call('permission:cache-reset');
        $this->newLine();

        $this->info('✅ Permission setup completed successfully!');
        $this->newLine();

        $this->displaySummary();

        return self::SUCCESS;
    }

    /**
     * @return array{'created': list<string>, 'existing': list<string>, 'deleted': int}
     *
     * @throws Throwable
     */
    private function syncPermissions(string $guardName = 'web'): array
    {
        return DB::transaction(function () use ($guardName): array {
            $created = [];
            $existing = [];

            foreach (PermissionEnum::cases() as $permissionEnum) {
                $permission = Permission::query()->firstOrCreate([
                    'name' => $permissionEnum->value,
                    'guard_name' => $guardName,
                ]);

                if ($permission->wasRecentlyCreated) {
                    $created[] = $permissionEnum->value;
                } else {
                    $existing[] = $permissionEnum->value;
                }
            }

            // Clean up permissions that no longer exist in enum
            $enumPermissions = array_map(
                fn (PermissionEnum $case) => $case->value,
                PermissionEnum::cases()
            );
            /** @var int $deletedCount */
            $deletedCount = Permission::query()
                ->where('guard_name', $guardName)
                ->whereNotIn('name', $enumPermissions)
                ->delete();

            return [
                'created' => $created,
                'existing' => $existing,
                'deleted' => $deletedCount,
            ];
        });
    }

    /**
     * @throws Throwable
     */
    private function assignPermissionsToRoles(): void
    {
        DB::transaction(function (): void {
            foreach (RoleEnum::cases() as $roleEnum) {
                $role = Role::query()->firstOrCreate([
                    'name' => $roleEnum->value,
                    'guard_name' => 'web',
                ]);

                $permissions = PermissionEnum::forRole($roleEnum);

                $role->syncPermissions($permissions);
            }
        });
    }

    private function createAdminUser(CreateUser $createUser): void
    {
        // CLI Mode: Try once with provided options
        if ($this->option('admin-email') && $this->option('admin-password') && $this->option('admin-name')) {
            $this->processUserCreation(
                $createUser,
                (string) $this->option('admin-name'),
                (string) $this->option('admin-email'),
                (string) $this->option('admin-password'),
                (string) $this->option('admin-password')
            );

            return;
        }

        // Interactive Mode: Loop until success or user cancellation
        do {
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

            if ($this->processUserCreation($createUser, $name, $email, $password, $passwordConfirmation)) {
                break;
            }

            if (! $this->confirm('   Would you like to try again?', true)) {
                break;
            }
        } while (true);
    }

    private function processUserCreation(
        CreateUser $createUser,
        string $name,
        string $email,
        string $password,
        string $passwordConfirmation
    ): bool {
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
            'role' => RoleEnum::ADMIN->value,
        ], (new CreateUserRequest)->rules());

        if ($validator->fails()) {
            $this->error('   ❌ Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line('      - '.$error);
            }

            return false;
        }

        try {
            $admin = $createUser->handle(
                ['name' => $name, 'email' => $email],
                $password
            );

            $admin->assignRole(RoleEnum::ADMIN->value);
            $admin->givePermissionTo(Permission::all());

            $this->newLine();
            $this->info('   ✅ Admin user created successfully!');
            $this->line('   ✓ Name: '.$admin->name);
            $this->line('   ✓ Email: '.$admin->email);
            $this->line('   ✓ Password: '.str_repeat('•', 8));
            $this->line('   ✓ Role: '.RoleEnum::ADMIN->value);

            return true;
        } catch (Throwable $throwable) {
            $this->error('   ❌ Failed to create admin user: '.$throwable->getMessage());

            return false;
        }
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
            $role = Role::query()->firstOrCreate([
                'name' => $roleEnum->value,
                'guard_name' => 'web',
            ]);

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
