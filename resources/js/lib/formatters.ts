/**
 * Format an integer (smallest currency unit) to a display string.
 * Amounts are stored as integers — divide by 100.
 *
 * @example formatMoney(150000) → "1,500.00"
 */
export function formatMoney(
    amount: number,
    currency = 'DZD',
    locale = 'fr-DZ',
): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
    }).format(amount / 100);
}

/**
 * Compact formatter — no currency symbol, always 2 decimal places.
 */
export function formatAmount(amount: number): string {
    return new Intl.NumberFormat('fr-DZ', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount / 100);
}

/**
 * Format a Laravel timestamp string to a human-readable date.
 */
export function formatDate(dateStr: string | null): string {
    if (!dateStr) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(dateStr));
}

/**
 * Format a Laravel timestamp string to date + time.
 */
export function formatDateTime(dateStr: string | null): string {
    if (!dateStr) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(dateStr));
}

/**
 * Relative time — e.g. "3 days ago"
 */
export function formatRelativeTime(dateStr: string): string {
    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
    const diff = (new Date(dateStr).getTime() - Date.now()) / 1000;
    const units: [Intl.RelativeTimeFormatUnit, number][] = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
        ['second', 1],
    ];
    for (const [unit, threshold] of units) {
        if (Math.abs(diff) >= threshold) {
            return rtf.format(Math.round(diff / threshold), unit);
        }
    }
    return 'just now';
}
