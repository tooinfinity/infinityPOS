import { Column } from '@tanstack/react-table';
import { ArrowDown, ArrowUp, ChevronsUpDown, EyeOff } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';

interface Props<TData, TValue> {
    column: Column<TData, TValue>;
    title: string;
    className?: string;
}

export default function DataTableColumnHeader<TData, TValue>({
    column,
    title,
    className,
}: Props<TData, TValue>) {
    if (!column.getCanSort()) {
        return (
            <span className={cn('text-xs font-medium', className)}>
                {title}
            </span>
        );
    }

    return (
        <div className={cn('flex items-center gap-1', className)}>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="-ml-3 h-8 text-xs font-medium data-[state=open]:bg-accent"
                    >
                        {title}
                        {column.getIsSorted() === 'desc' ? (
                            <ArrowDown className="ml-1.5 h-3 w-3" />
                        ) : column.getIsSorted() === 'asc' ? (
                            <ArrowUp className="ml-1.5 h-3 w-3" />
                        ) : (
                            <ChevronsUpDown className="ml-1.5 h-3 w-3 opacity-40" />
                        )}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start">
                    <DropdownMenuItem
                        onClick={() => column.toggleSorting(false)}
                    >
                        <ArrowUp className="mr-2 h-3.5 w-3.5 text-muted-foreground" />{' '}
                        Asc
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        onClick={() => column.toggleSorting(true)}
                    >
                        <ArrowDown className="mr-2 h-3.5 w-3.5 text-muted-foreground" />{' '}
                        Desc
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        onClick={() => column.toggleVisibility(false)}
                    >
                        <EyeOff className="mr-2 h-3.5 w-3.5 text-muted-foreground" />{' '}
                        Hide
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
