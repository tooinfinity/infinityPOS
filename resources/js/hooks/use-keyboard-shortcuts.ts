import { useEffect } from 'react';

interface KeyboardShortcutsOptions {
    onPayNow?: () => void;
    onSearchFocus?: () => void;
    onClearCart?: () => void;
    enabled?: boolean;
}

export function useKeyboardShortcuts({
    onPayNow,
    onSearchFocus,
    onClearCart,
    enabled = true,
}: KeyboardShortcutsOptions) {
    useEffect(() => {
        if (!enabled) return;

        const handleKeyDown = (e: KeyboardEvent) => {
            // Don't trigger shortcuts when typing in input fields
            const isInInput =
                e.target instanceof HTMLInputElement ||
                e.target instanceof HTMLTextAreaElement;

            if (isInInput) return;

            switch (e.key) {
                case 'Enter':
                    e.preventDefault();
                    onPayNow?.();
                    break;
                case 'F2':
                    e.preventDefault();
                    onSearchFocus?.();
                    break;
                case 'Escape':
                    e.preventDefault();
                    onClearCart?.();
                    break;
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [onPayNow, onSearchFocus, onClearCart, enabled]);
}
