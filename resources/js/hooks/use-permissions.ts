import type { Auth } from '@/types';
import { usePage } from '@inertiajs/react';

interface SharedProps {
    auth: Auth;
    [key: string]: unknown;
}

export function usePermissions() {
    const { auth } = usePage<SharedProps>().props;

    const permissions = new Set(auth.user?.permissions ?? []);
    const roles = new Set(auth.user?.roles ?? []);

    const permissionsSet = new Set(permissions);
    const rolesSet = new Set(roles);

    return {
        can: (permission: string) => {
            return permissionsSet.has(permission);
        },

        isRole: (role: string) => {
            return rolesSet.has(role);
        },

        isAdmin: rolesSet.has('admin'),
        isManager: rolesSet.has('manager'),
        isCashier: rolesSet.has('cashier'),
    };
}
