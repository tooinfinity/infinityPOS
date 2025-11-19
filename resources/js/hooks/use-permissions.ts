import { Auth } from '@/types';
import { usePage } from '@inertiajs/react';

export function usePermissions() {
    const { auth } = usePage<{ auth: Auth }>().props;

    const hasPermission = (permission: string): boolean => {
        return auth.user?.permissions.includes(permission) ?? false;
    };

    const hasAnyPermission = (permissions: string[]): boolean => {
        return permissions.some((permission) => hasPermission(permission));
    };

    const hasRole = (role: string): boolean => {
        return auth.user?.roles.includes(role) ?? false;
    };

    const hasAnyRole = (roles: string[]): boolean => {
        return roles.some((role) => hasRole(role));
    };

    return {
        hasPermission,
        hasAnyPermission,
        hasRole,
        hasAnyRole,
        permissions: auth.user?.permissions ?? [],
        roles: auth.user?.roles ?? [],
    };
}
