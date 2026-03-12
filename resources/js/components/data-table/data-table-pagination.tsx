import { router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Paginated } from '@/lib/paginated';

interface Props<T> {
    pagination: Paginated<T>;
    /** Wayfinder base URL — e.g. SaleController.index.url() */
    baseUrl: string;
    filters?: Record<string, unknown>;
}

const PER_PAGE_OPTIONS = [15, 25, 50, 100];

export default function DataTablePagination<T>({
    pagination,
    baseUrl,
    filters = {},
}: Props<T>) {
    const { current_page, last_page, per_page, from, to, total } = pagination;

    function go(page: number) {
        router.get(
            baseUrl,
            { ...filters, page },
            { preserveState: true, replace: true },
        );
    }

    function changePerPage(value: string) {
        router.get(
            baseUrl,
            { ...filters, per_page: Number(value), page: 1 },
            { preserveState: true, replace: true },
        );
    }

    return (
        <div className="flex items-center justify-between px-1 py-1">
            <p className="text-sm text-muted-foreground">
                {from != null && to != null ? (
                    <>
                        Showing <span className="font-medium">{from}</span>–
                        <span className="font-medium">{to}</span> of{' '}
                        <span className="font-medium">{total}</span>
                    </>
                ) : (
                    'No results'
                )}
            </p>

            <div className="flex items-center gap-4">
                <div className="flex items-center gap-2">
                    <span className="text-sm whitespace-nowrap text-muted-foreground">
                        Rows per page
                    </span>
                    <Select
                        value={String(per_page)}
                        onValueChange={changePerPage}
                    >
                        <SelectTrigger className="h-8 w-16">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {PER_PAGE_OPTIONS.map((n) => (
                                <SelectItem key={n} value={String(n)}>
                                    {n}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="flex items-center gap-1">
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8"
                        onClick={() => go(1)}
                        disabled={current_page === 1}
                    >
                        <ChevronsLeft className="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8"
                        onClick={() => go(current_page - 1)}
                        disabled={current_page === 1}
                    >
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <span className="px-2 text-sm font-medium tabular-nums">
                        {current_page} / {last_page}
                    </span>
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8"
                        onClick={() => go(current_page + 1)}
                        disabled={current_page === last_page}
                    >
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8"
                        onClick={() => go(last_page)}
                        disabled={current_page === last_page}
                    >
                        <ChevronsRight className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
