import { Button } from '@/components/ui/button';
import { usePermissions } from '@/hooks/use-permissions';
import type { ButtonHTMLAttributes, ReactNode } from 'react';

interface PermissionButtonProps
    extends ButtonHTMLAttributes<HTMLButtonElement> {
    permission?: string;
    permissions?: string[];
    children: ReactNode;
    className?: string;
}

export function PermissionButton({
    permission,
    permissions,
    children,
    className = '',
    ...props
}: PermissionButtonProps): ReactNode {
    const { hasPermission, hasAnyPermission } = usePermissions();

    let hasAccess = false;

    if (permission) {
        hasAccess = hasPermission(permission);
    } else if (permissions && permissions.length > 0) {
        hasAccess = hasAnyPermission(...permissions);
    }

    if (!hasAccess) {
        return null;
    }

    return (
        <Button className={className} {...props}>
            {children}
        </Button>
    );
}
