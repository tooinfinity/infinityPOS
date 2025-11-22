import { usePermissions } from '@/hooks/use-permissions';
import type { ReactNode } from 'react';

interface CanProps {
    permission?: string;
    role?: string;
    children: ReactNode;
}

export function Can({ permission, role, children }: CanProps) {
    const { can, isRole } = usePermissions();

    if (permission) {
        const hasPermission = can(permission);
        if (!hasPermission) return null;
        return children;
    }

    if (role) {
        const hasRole = isRole(role);
        if (!hasRole) return null;
        return children;
    }

    return null;
}
