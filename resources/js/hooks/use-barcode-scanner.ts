import { useEffect, useRef } from 'react';

interface Product {
    id: number;
    name: string;
    sku: string | null;
    barcode: string | null;
}

interface BarcodeScannerOptions {
    products: Product[];
    onProductFound: (product: Product) => void;
    searchInputRef?: React.RefObject<HTMLInputElement>;
    onSearchClear?: () => void;
}

export function useBarcodeScanner({
    products,
    onProductFound,
    searchInputRef,
    onSearchClear,
}: BarcodeScannerOptions) {
    const barcodeBufferRef = useRef('');
    const lastKeyTimeRef = useRef(Date.now());

    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            const isInInput =
                e.target instanceof HTMLInputElement ||
                e.target instanceof HTMLTextAreaElement;

            // Barcode scanner detection
            const currentTime = Date.now();
            const timeDiff = currentTime - lastKeyTimeRef.current;

            // Barcode scanners type very fast (< 100ms between characters)
            if (timeDiff > 100) {
                barcodeBufferRef.current = '';
            }
            lastKeyTimeRef.current = currentTime;

            // Build barcode buffer
            if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                barcodeBufferRef.current += e.key;
            }

            // On Enter, check if we have a barcode
            if (e.key === 'Enter' && barcodeBufferRef.current.length >= 3) {
                const barcode = barcodeBufferRef.current.trim();
                const product = products.find(
                    (p) =>
                        p.barcode?.toLowerCase() === barcode.toLowerCase() ||
                        p.sku?.toLowerCase() === barcode.toLowerCase(),
                );

                if (product) {
                    e.preventDefault();
                    onProductFound(product);

                    // Clear search if barcode was in search field
                    if (
                        searchInputRef?.current &&
                        e.target === searchInputRef.current &&
                        onSearchClear
                    ) {
                        onSearchClear();
                    }

                    // Refocus search for next scan
                    setTimeout(() => searchInputRef?.current?.focus(), 100);
                }
                barcodeBufferRef.current = '';
                return;
            }

            // Don't trigger other shortcuts in input fields
            if (isInInput) {
                return;
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [products, onProductFound, searchInputRef, onSearchClear]);
}
