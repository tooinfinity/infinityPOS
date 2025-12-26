import { cn } from '@/lib/utils';

interface ShortcutItem {
    key: string;
    label: string;
}

const shortcuts: ShortcutItem[] = [
    { key: 'Enter', label: 'Pay Now' },
    { key: 'F2', label: 'Search' },
    { key: 'Esc', label: 'Clear Cart' },
];

export function KeyboardShortcutsBar() {
    return (
        <div className="border-t border-border/50 bg-muted/30 px-4 py-2">
            <div className="flex items-center justify-center gap-5 text-xs text-muted-foreground">
                {shortcuts.map((shortcut) => (
                    <div key={shortcut.key} className="flex items-center gap-2">
                        <kbd
                            className={cn(
                                'rounded border border-border/60 bg-background px-2 py-0.5',
                                'font-mono text-xs font-semibold',
                            )}
                        >
                            {shortcut.key}
                        </kbd>
                        <span>{shortcut.label}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}
