import { cn } from '@/lib/utils';

interface Category {
    id: number;
    name: string;
    code: string;
}

interface CategoryFilterProps {
    categories: Category[];
    activeCategory: number | null;
    onCategoryChange: (categoryId: number | null) => void;
}

export function CategoryFilter({
    categories,
    activeCategory,
    onCategoryChange,
}: CategoryFilterProps) {
    return (
        <div className="flex items-center gap-2 overflow-x-auto pb-2">
            <span className="text-sm font-medium whitespace-nowrap text-muted-foreground">
                Category:
            </span>

            <button
                onClick={() => onCategoryChange(null)}
                className={cn(
                    'rounded-lg px-4 py-2 text-sm font-medium whitespace-nowrap transition-all duration-200',
                    activeCategory === null
                        ? 'bg-primary text-primary-foreground shadow-sm'
                        : 'bg-secondary/50 text-secondary-foreground hover:bg-secondary',
                )}
            >
                All
            </button>

            {categories.map((category) => (
                <button
                    key={category.id}
                    onClick={() => onCategoryChange(category.id)}
                    className={cn(
                        'rounded-lg px-4 py-2 text-sm font-medium whitespace-nowrap transition-all duration-200',
                        activeCategory === category.id
                            ? 'bg-primary text-primary-foreground shadow-sm'
                            : 'bg-secondary/50 text-secondary-foreground hover:bg-secondary',
                    )}
                >
                    {category.name}
                </button>
            ))}
        </div>
    );
}
