import { usePermissions } from '@/hooks/use-permissions';
import { router } from '@inertiajs/react';
import { useEffect, type ReactNode } from 'react';

interface CanProps {
    permission?: string;
    permissions?: string[];
    children: ReactNode;
    fallback?: ReactNode;
}

export function Can({
    permission,
    permissions,
    children,
    fallback = null,
}: CanProps): ReactNode {
    const { hasPermission, hasAnyPermission } = usePermissions();

    let hasAccess = false;

    if (permission) {
        hasAccess = hasPermission(permission);
    } else if (permissions && permissions.length > 0) {
        hasAccess = hasAnyPermission(...permissions);
    }

    if (!hasAccess && fallback) {
        return fallback;
    }

    return hasAccess ? children : null;
}

interface CanAllProps {
    permissions: string[];
    children: ReactNode;
    fallback?: ReactNode;
}

export function CanAll({
    permissions,
    children,
    fallback = null,
}: CanAllProps): ReactNode {
    const { hasAllPermissions } = usePermissions();
    const hasAccess = hasAllPermissions(...permissions);

    if (!hasAccess && fallback) {
        return fallback;
    }

    return hasAccess ? children : null;
}

interface HasRoleProps {
    role?: string;
    roles?: string[];
    children: ReactNode;
    fallback?: ReactNode;
}

export function HasRole({
    role,
    roles,
    children,
    fallback = null,
}: HasRoleProps): ReactNode {
    const { hasRole: checkRole, hasAnyRole } = usePermissions();

    let hasAccess = false;

    if (role) {
        hasAccess = checkRole(role);
    } else if (roles && roles.length > 0) {
        hasAccess = hasAnyRole(...roles);
    }

    if (!hasAccess && fallback) {
        return fallback;
    }

    return hasAccess ? children : null;
}

interface ProtectedPageProps {
    permission?: string;
    permissions?: string[];
    role?: string;
    roles?: string[];
    children: ReactNode;
    redirectTo?: string;
}

export function ProtectedPage({
    permission,
    permissions,
    role,
    roles,
    children,
    redirectTo = '/dashboard',
}: ProtectedPageProps): ReactNode {
    const { hasPermission, hasAnyPermission, hasRole, hasAnyRole } =
        usePermissions();

    let hasAccess = true;

    if (permission) {
        hasAccess = hasPermission(permission);
    } else if (permissions && permissions.length > 0) {
        hasAccess = hasAnyPermission(...permissions);
    } else if (role) {
        hasAccess = hasRole(role);
    } else if (roles && roles.length > 0) {
        hasAccess = hasAnyRole(...roles);
    }

    useEffect(() => {
        if (!hasAccess) {
            router.visit(redirectTo);
        }
    }, [hasAccess, redirectTo]);

    if (!hasAccess) {
        return null;
    }

    return children;
}
