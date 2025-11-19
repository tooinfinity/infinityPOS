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

    let authorized = true;
    if (permission) authorized = authorized && hasPermission(permission);
    if (permissions) authorized = authorized && hasAnyPermission(permissions);
    if (role) authorized = authorized && hasRole(role);
    if (roles) authorized = authorized && hasAnyRole(roles);

    return <>{authorized ? children : fallback}</>;
}
