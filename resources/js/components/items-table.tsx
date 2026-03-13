import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { LucideIcon } from 'lucide-react';

export interface ItemsTableColumn<T> {
    key: keyof T | string;
    header: string;
    align?: 'left' | 'right' | 'center';
    render?: (item: T) => React.ReactNode;
}

interface ItemsTableProps<T> {
    items: T[];
    columns: ItemsTableColumn<T>[];
    title?: string;
    icon?: LucideIcon;
    emptyText?: string;
    wrapper?: 'card' | 'border';
}

export default function ItemsTable<T>({
    items,
    columns,
    emptyText = 'No items',
    wrapper = 'border',
}: ItemsTableProps<T>) {
    const content = (
        <Table>
            <TableHeader>
                <TableRow>
                    {columns.map((column) => (
                        <TableHead
                            key={String(column.key)}
                            className={
                                column.align === 'right'
                                    ? 'text-right'
                                    : column.align === 'center'
                                      ? 'text-center'
                                      : undefined
                            }
                        >
                            {column.header}
                        </TableHead>
                    ))}
                </TableRow>
            </TableHeader>
            <TableBody>
                {items.length === 0 ? (
                    <TableRow>
                        <TableCell
                            colSpan={columns.length}
                            className="h-24 text-center text-muted-foreground"
                        >
                            {emptyText}
                        </TableCell>
                    </TableRow>
                ) : (
                    items.map((item, index) => (
                        <TableRow key={index}>
                            {columns.map((column) => (
                                <TableCell
                                    key={String(column.key)}
                                    className={
                                        column.align === 'right'
                                            ? 'text-right'
                                            : column.align === 'center'
                                              ? 'text-center'
                                              : undefined
                                    }
                                >
                                    {column.render
                                        ? column.render(item)
                                        : String(
                                              item[column.key as keyof T] ?? '',
                                          )}
                                </TableCell>
                            ))}
                        </TableRow>
                    ))
                )}
            </TableBody>
        </Table>
    );

    if (wrapper === 'card') {
        return (
            <div className="rounded-md border">
                <div className="rounded-lg border">{content}</div>
            </div>
        );
    }

    return <div className="rounded-md border">{content}</div>;
}
