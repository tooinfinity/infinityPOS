/**
 * Laravel paginator shape forwarded by Inertia.
 * Wayfinder generates model types; this wrapper is the only manual type we need.
 */
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}
