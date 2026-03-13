import { Input } from '@/components/ui/input';

interface FilterBarProps {
    search: string;
    onSearchChange: (value: string) => void;
    onSearch: () => void;
    placeholder?: string;
    children?: React.ReactNode;
}

export default function FilterBar({
    search,
    onSearchChange,
    onSearch,
    placeholder = 'Search...',
    children,
}: FilterBarProps) {
    return (
        <div className="flex flex-wrap items-center gap-3">
            <Input
                placeholder={placeholder}
                className="h-9 w-64"
                value={search}
                onChange={(e) => onSearchChange(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && onSearch()}
                onBlur={onSearch}
            />
            {children}
        </div>
    );
}
