<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

final readonly class SyncPermissions
{
    /**
     * Execute the action.
     *
     * @return array{'created': list<string>, 'existing': list<string>, 'deleted': int}
     *
     * @throws Throwable
     */
    public function handle(string $guardName = 'web'): array
    {
        return DB::transaction(function () use ($guardName): array {
            $created = [];
            $existing = [];

            foreach (PermissionEnum::cases() as $permissionEnum) {
                $permission = Permission::query()->firstOrCreate(
                    ['name' => $permissionEnum->value],
                    ['guard_name' => $guardName]
                );

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
                ->whereNotIn('name', $enumPermissions)
                ->delete();

            app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

            Log::info('Permissions synced successfully', [
                'created' => count($created),
                'existing' => count($existing),
                'deleted' => $deletedCount,
            ]);

            return [
                'created' => $created,
                'existing' => $existing,
                'deleted' => $deletedCount,
            ];
        });

    }
}
