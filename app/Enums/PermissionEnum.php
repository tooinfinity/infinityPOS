<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionEnum: string
{
    case CREATE_USER = 'create_user';
    case UPDATE_USER = 'update_user';
    case VIEW_USER = 'view_user';
    case DELETE_USER = 'delete_user';

    /**
     * @return array<string, array{label: string, group: string}>
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $permission): array => [
                $permission->value => [
                    'label' => $permission->label(),
                    'group' => $permission->group()->label(),
                ],
            ])
            ->all();
    }

    /**
     * @return array<string, array<int, array{value: string, label: string}>>
     */
    public static function groupedArray(): array
    {
        /** @var array<string, array<int, array{value: string, label: string}>> $result */
        $result = [];

        foreach (self::cases() as $permission) {
            $group = $permission->group()->label();

            $result[$group] ??= [];

            $result[$group][] = [
                'value' => $permission->value,
                'label' => $permission->label(),
            ];
        }

        return $result;
    }

    public function label(): string
    {
        return match ($this) {
            self::CREATE_USER => 'Create User',
            self::UPDATE_USER => 'Update User',
            self::VIEW_USER => 'View User',
            self::DELETE_USER => 'Delete User',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CREATE_USER => 'Create new user',
            self::UPDATE_USER => 'Update existing user',
            self::VIEW_USER => 'View user details',
            self::DELETE_USER => 'Delete user',
        };
    }

    public function group(): PermissionGroupEnum
    {
        return match ($this) {
            self::CREATE_USER, self::UPDATE_USER, self::VIEW_USER, self::DELETE_USER => PermissionGroupEnum::USERS
            // add more groups here
        };
    }
}
