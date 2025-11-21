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
        return (auth.user?.is_admin as boolean) ?? false;
    };

    const isManager = (): boolean => {
        return (auth.user?.is_manager as boolean) ?? false;
    };

    const isCashier = (): boolean => {
        return (auth.user?.is_cashier as boolean) ?? false;
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
