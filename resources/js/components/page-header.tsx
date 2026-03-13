import { router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

import { Button } from '@/components/ui/button';

interface PageHeaderProps {
    backUrl: string;
    title: string;
    badges?: React.ReactNode;
    subtitle?: React.ReactNode;
    actions?: React.ReactNode;
}

export default function PageHeader({
    backUrl,
    title,
    badges,
    subtitle,
    actions,
}: PageHeaderProps) {
    return (
        <div className="flex items-start justify-between gap-4">
            <div className="flex items-center gap-3">
                <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 shrink-0"
                    onClick={() => router.visit(backUrl)}
                >
                    <ArrowLeft className="h-4 w-4" />
                </Button>
                <div>
                    <div className="flex items-center gap-2.5">
                        <h1 className="text-xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        {badges}
                    </div>
                    {subtitle && (
                        <p className="mt-0.5 text-sm text-muted-foreground">
                            {subtitle}
                        </p>
                    )}
                </div>
            </div>
            {actions && (
                <div className="flex shrink-0 items-center gap-2">
                    {actions}
                </div>
            )}
        </div>
    );
}
