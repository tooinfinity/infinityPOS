import { cn } from '@/lib/utils';
import type { App } from '@/wayfinder/types';

// ─── Internal ─────────────────────────────────────────────────────────────────

function StatusPill({
    label,
    className,
}: {
    label: string;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs leading-none font-semibold ring-1 ring-inset',
                className,
            )}
        >
            {label}
        </span>
    );
}

// ─── Sale ─────────────────────────────────────────────────────────────────────

const SALE_STATUS: Record<
    App.Enums.SaleStatusEnum,
    { label: string; cls: string }
> = {
    pending: {
        label: 'Pending',
        cls: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/20 dark:text-amber-400',
    },
    completed: {
        label: 'Completed',
        cls: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
    cancelled: {
        label: 'Cancelled',
        cls: 'bg-zinc-100 text-zinc-500 ring-zinc-500/20 dark:bg-zinc-800 dark:text-zinc-400',
    },
};

export function SaleStatusBadge({
    status,
    className,
}: {
    status: App.Enums.SaleStatusEnum;
    className?: string;
}) {
    const { label, cls } = SALE_STATUS[status];
    return <StatusPill label={label} className={cn(cls, className)} />;
}

// ─── Purchase ─────────────────────────────────────────────────────────────────

const PURCHASE_STATUS: Record<
    App.Enums.PurchaseStatusEnum,
    { label: string; cls: string }
> = {
    pending: {
        label: 'Pending',
        cls: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/20 dark:text-amber-400',
    },
    ordered: {
        label: 'Ordered',
        cls: 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400',
    },
    received: {
        label: 'Received',
        cls: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
    cancelled: {
        label: 'Cancelled',
        cls: 'bg-zinc-100 text-zinc-500 ring-zinc-500/20 dark:bg-zinc-800 dark:text-zinc-400',
    },
};

export function PurchaseStatusBadge({
    status,
    className,
}: {
    status: App.Enums.PurchaseStatusEnum;
    className?: string;
}) {
    const { label, cls } = PURCHASE_STATUS[status];
    return <StatusPill label={label} className={cn(cls, className)} />;
}

// ─── Payment ──────────────────────────────────────────────────────────────────

const PAYMENT_STATUS: Record<
    App.Enums.PaymentStatusEnum,
    { label: string; cls: string }
> = {
    unpaid: {
        label: 'Unpaid',
        cls: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/20 dark:text-red-400',
    },
    partial: {
        label: 'Partial',
        cls: 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400',
    },
    paid: {
        label: 'Paid',
        cls: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
};

export function PaymentStatusBadge({
    status,
    className,
}: {
    status: App.Enums.PaymentStatusEnum;
    className?: string;
}) {
    const { label, cls } = PAYMENT_STATUS[status];
    return <StatusPill label={label} className={cn(cls, className)} />;
}

// ─── Return ───────────────────────────────────────────────────────────────────

const RETURN_STATUS: Record<
    App.Enums.ReturnStatusEnum,
    { label: string; cls: string }
> = {
    pending: {
        label: 'Pending',
        cls: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/20 dark:text-amber-400',
    },
    completed: {
        label: 'Completed',
        cls: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
};

export function ReturnStatusBadge({
    status,
    className,
}: {
    status: App.Enums.ReturnStatusEnum;
    className?: string;
}) {
    const { label, cls } = RETURN_STATUS[status];
    return <StatusPill label={label} className={cn(cls, className)} />;
}

// ─── Active ───────────────────────────────────────────────────────────────────

export function ActiveBadge({
    active,
    className,
}: {
    active: boolean;
    className?: string;
}) {
    return (
        <StatusPill
            label={active ? 'Active' : 'Inactive'}
            className={cn(
                active
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400'
                    : 'bg-zinc-100 text-zinc-500 ring-zinc-500/20 dark:bg-zinc-800 dark:text-zinc-400',
                className,
            )}
        />
    );
}

// ─── Stock ───────────────────────────────────────────────────────────────────

export type StockStatus = 'in_stock' | 'low_stock' | 'out_of_stock';

const STOCK_STATUS: Record<StockStatus, { label: string; cls: string }> = {
    in_stock: {
        label: 'In Stock',
        cls: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400',
    },
    low_stock: {
        label: 'Low Stock',
        cls: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/20 dark:text-amber-400',
    },
    out_of_stock: {
        label: 'Out of Stock',
        cls: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/20 dark:text-red-400',
    },
};

export function StockStatusBadge({
    status,
    className,
}: {
    status: StockStatus;
    className?: string;
}) {
    const { label, cls } = STOCK_STATUS[status];
    return <StatusPill label={label} className={cn(cls, className)} />;
}
