import type { Auth } from '@/types';
import { usePage } from '@inertiajs/react';

interface UsePermissionsReturn {
    permissions: string[];
    roles: string[];
    hasPermission: (permission: string) => boolean;
    hasAnyPermission: (...permissions: string[]) => boolean;
    hasAllPermissions: (...permissions: string[]) => boolean;
    hasRole: (role: string) => boolean;
    hasAnyRole: (...roles: string[]) => boolean;
    hasAllRoles: (...roles: string[]) => boolean;
    isAdmin: () => boolean;
    isManager: () => boolean;
    isCashier: () => boolean;
}

export function usePermissions(): UsePermissionsReturn {
    const { auth } = usePage<{ auth: Auth }>().props;

    const hasPermission = (permission: string): boolean => {
        return auth.user?.permissions.includes(permission) ?? false;
    };

    const hasAnyPermission = (...permissions: string[]): boolean => {
        return permissions.some((permission) => hasPermission(permission));
    };

    const hasAllPermissions = (...permissions: string[]): boolean => {
        return permissions.every((permission) => hasPermission(permission));
    };

    const hasRole = (role: string): boolean => {
        return auth.user?.roles?.includes(role) ?? false;
    };

    const hasAnyRole = (...roles: string[]): boolean => {
        return roles.some((role) => hasRole(role));
    };

    const hasAllRoles = (...roles: string[]): boolean => {
        return roles.every((role) => hasRole(role));
    };

    const isAdmin = (): boolean => {
        return <boolean>auth.user?.is_admin ?? false;
    };

    const isManager = (): boolean => {
        return <boolean>auth.user?.is_manager ?? false;
    };

    const isCashier = (): boolean => {
        return <boolean>auth.user?.is_cashier ?? false;
    };

    return {
        permissions: auth.user?.permissions ?? [],
        roles: auth.user?.roles ?? [],
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasRole,
        hasAnyRole,
        hasAllRoles,
        isAdmin,
        isManager,
        isCashier,
    };
}
