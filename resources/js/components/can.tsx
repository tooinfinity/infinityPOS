import { usePermissions } from '@/hooks/use-permissions';

interface CanProps {
    permission?: string;
    permissions?: string[];
    role?: string;
    roles?: string[];
    children: React.ReactNode;
    fallback?: React.ReactNode;
}

export function Can({
    permission,
    permissions,
    role,
    roles,
    children,
    fallback = null,
}: CanProps) {
    const { hasPermission, hasAnyPermission, hasRole, hasAnyRole } =
        usePermissions();

    let authorized = false;

    if (permission) {
        authorized = hasPermission(permission);
    } else if (permissions) {
        authorized = hasAnyPermission(permissions);
    } else if (role) {
        authorized = hasRole(role);
    } else if (roles) {
        authorized = hasAnyRole(roles);
    }

    return <>{authorized ? children : fallback}</>;
}
