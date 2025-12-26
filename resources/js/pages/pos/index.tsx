import { CartSidebar } from '@/components/pos/cart-sidebar';
import { CategoryFilter } from '@/components/pos/category-filter';
import {
    CustomerSelector,
    QuickAddCustomerModal,
} from '@/components/pos/customer-selector';
import { KeyboardShortcutsBar } from '@/components/pos/keyboard-shortcuts-bar';
import { PaymentModal } from '@/components/pos/payment-modal';
import { ProductCard } from '@/components/pos/product-card';
import { RegisterSetupModal } from '@/components/pos/register-setup-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useBarcodeScanner } from '@/hooks/use-barcode-scanner';
import { useCustomerManagement } from '@/hooks/use-customer-management';
import { useKeyboardShortcuts } from '@/hooks/use-keyboard-shortcuts';
import { useLanguage } from '@/hooks/use-language';
import { usePaymentProcessing } from '@/hooks/use-payment-processing';
import { usePosCart } from '@/hooks/use-pos-cart';
import { usePosModals } from '@/hooks/use-pos-modals';
import type { Customer, PosIndexProps, Product } from '@/types/pos';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { ArrowLeft, Search, Settings, UserPlus } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

// Re-export Product type for backward compatibility
export type { Product } from '@/types/pos';

export default function PosIndex({
    products: initialProducts,
    categories,
    register,
    settings,
    stores = [],
    moneyboxes = [],
}: PosIndexProps) {
    const { __ } = useLanguage();

    // UI State
    const [activeCategory, setActiveCategory] = useState<number | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [products, setProducts] = useState<Product[]>(initialProducts);
    const [isSearching, setIsSearching] = useState(false);
    const searchInputRef = useRef<HTMLInputElement>(null);

    // Customer Management Hook
    const { selectedCustomer, selectCustomer, clearCustomer } =
        useCustomerManagement();

    // Wrapper to handle null customer (for CustomerSelector compatibility)
    const handleCustomerChange = useCallback(
        (customer: Customer | null) => {
            if (customer === null) {
                clearCustomer();
            } else {
                selectCustomer(customer);
            }
        },
        [selectCustomer, clearCustomer],
    );

    // Custom Hooks
    const {
        cart,
        isLoading: isLoadingCart,
        loadCart,
        addToCart,
        updateQuantity,
        removeItem,
        clearCart: clearCartAction,
        clearCartState,
    } = usePosCart(products);

    // Search products via API
    const searchProducts = useCallback(
        async (query: string) => {
            if (!query.trim()) {
                setProducts(initialProducts);
                setIsSearching(false);
                return;
            }

            setIsSearching(true);
            try {
                const response = await axios.get('/pos/products', {
                    params: { query: query.trim() },
                });
                setProducts(response.data.data);
            } catch (error) {
                console.error('Failed to search products:', error);
                toast.error(__('Failed to search products'));
                setProducts(initialProducts);
            } finally {
                setIsSearching(false);
            }
        },
        [initialProducts, __],
    );

    // Debounced search effect
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            searchProducts(searchQuery);
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [searchQuery, searchProducts]);

    const modals = usePosModals();

    // Handle discount application
    const handleApplyDiscount = useCallback(
        async (discountAmount: number) => {
            try {
                await axios.put('/pos/cart/discount', {
                    discount: discountAmount,
                });
                await loadCart();
            } catch (error) {
                console.error('Failed to apply discount:', error);
                toast.error(__('Failed to apply discount'));
            }
        },
        [loadCart, __],
    );

    // Handle tax application
    const handleApplyTax = useCallback(
        async (taxAmount: number) => {
            try {
                await axios.put('/pos/cart/tax', {
                    tax: taxAmount,
                });
                await loadCart();
            } catch (error) {
                console.error('Failed to apply tax:', error);
                toast.error(__('Failed to apply tax'));
            }
        },
        [loadCart, __],
    );

    // Load cart on mount and auto-focus search
    useEffect(() => {
        loadCart();
        searchInputRef.current?.focus();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []); // Only run once on mount

    const handlePayNow = useCallback(() => {
        if (cart.items.length === 0) return;
        modals.openPaymentModal();
    }, [cart.items.length, modals]);

    // Payment processing hook
    const { processQuickPayment } = usePaymentProcessing({
        onSuccess: () => {
            clearCartState();
            clearCustomer();
            toast.success(__('Payment completed successfully'));
            setTimeout(() => searchInputRef.current?.focus(), 500);
        },
    });

    const handleQuickPayExact = useCallback(async () => {
        if (cart.items.length === 0) return;

        const defaultMethod = settings.pos.default_payment_method;

        if (
            !confirm(
                __(
                    `Process exact payment of ${(cart.totals.total / 100).toFixed(2)} via ${defaultMethod}?`,
                ),
            )
        ) {
            return;
        }

        await processQuickPayment(
            register?.store_id ?? 0,
            defaultMethod as 'cash' | 'card' | 'transfer',
            cart.totals.total,
        );
    }, [
        cart.items.length,
        cart.totals.total,
        register,
        settings,
        __,
        processQuickPayment,
    ]);

    // Barcode Scanner Hook
    useBarcodeScanner({
        products,
        onProductFound: (product) => {
            addToCart(product.id);
            toast.success(`${__('Added')} ${product.name}`, {
                description: `${__('Quantity')}: 1`,
                duration: 2000,
            });
        },
        searchInputRef: searchInputRef as React.RefObject<HTMLInputElement>,
        onSearchClear: () => setSearchQuery(''),
    });

    // Keyboard Shortcuts Hook
    useKeyboardShortcuts({
        onPayNow: cart.items.length > 0 ? handlePayNow : undefined,
        onSearchFocus: () => {
            searchInputRef.current?.focus();
            searchInputRef.current?.select();
        },
        onClearCart: cart.items.length > 0 ? clearCartAction : undefined,
        enabled: true,
    });

    const handlePaymentSuccess = useCallback(() => {
        // Show success message
        toast.success(__('Payment completed successfully'));

        // Clear cart and customer
        clearCartState();
        clearCustomer();

        // Refocus search for next transaction
        setTimeout(() => searchInputRef.current?.focus(), 1000);
    }, [__, clearCartState, clearCustomer]);

    // Filter products by category (search is handled by API now)
    const filteredProducts = activeCategory
        ? products.filter((product) => product.category?.id === activeCategory)
        : products;

    const hasResults = filteredProducts.length > 0;
    const itemCount = cart.items.reduce((sum, item) => sum + item.quantity, 0);

    return (
        <>
            <Head title={__('Point of Sale')} />

            <div className="flex h-screen flex-col bg-background">
                {/* Header */}
                <header className="flex items-center justify-between border-b border-border/50 bg-card/50 px-3.5 py-2.5">
                    <div className="flex items-center gap-2.5">
                        {/* Back to Dashboard */}
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => router.visit('/dashboard')}
                            className="h-9 gap-2 px-3"
                            aria-label={__('Back to dashboard')}
                        >
                            <ArrowLeft className="h-4 w-4" />
                            <span className="hidden text-sm sm:inline">
                                {__('Dashboard')}
                            </span>
                        </Button>

                        <div className="hidden h-5 w-px bg-border sm:block" />

                        <div className="flex items-center gap-2">
                            <div className="rounded-lg bg-primary px-2.5 py-1 text-sm font-bold text-primary-foreground">
                                POS
                            </div>
                            <div className="hidden text-sm sm:block">
                                <div className="font-medium">
                                    {__('Point of Sale')}
                                </div>
                                {register && (
                                    <div className="text-xs text-muted-foreground">
                                        {register.name}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={modals.openRegisterModal}
                            aria-label={__('Register settings')}
                            className="h-9 w-9"
                        >
                            <Settings className="h-4.5 w-4.5" />
                        </Button>
                    </div>
                </header>

                {/* Main Content */}
                <div className="flex flex-1 flex-col overflow-hidden md:flex-row">
                    {/* Products Section */}
                    <div
                        className="flex flex-1 flex-col overflow-hidden"
                        role="main"
                        aria-label={__('Product selection')}
                    >
                        {/* Customer & Category Bar */}
                        <div className="space-y-2.5 border-b border-border/50 bg-card/50 px-3.5 py-2.5">
                            {/* Customer Selection */}
                            <div className="flex items-center gap-2">
                                <div className="relative flex-1">
                                    <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        ref={searchInputRef}
                                        type="text"
                                        placeholder={__(
                                            'Search products or scan barcode...',
                                        )}
                                        value={searchQuery}
                                        onChange={(e) =>
                                            setSearchQuery(e.target.value)
                                        }
                                        className="h-9 pr-3 pl-9 text-sm"
                                        aria-label={__('Search products')}
                                    />
                                </div>
                                <CustomerSelector
                                    selectedCustomer={selectedCustomer}
                                    onCustomerChange={handleCustomerChange}
                                    required={
                                        settings.sales.require_customer_for_sale
                                    }
                                />
                                <Button
                                    variant="outline"
                                    size="icon"
                                    onClick={modals.openQuickAddCustomer}
                                    title={__('Quick add customer')}
                                    className="h-9 w-9"
                                >
                                    <UserPlus className="h-4.5 w-4.5" />
                                </Button>
                            </div>

                            {/* Category Filter */}
                            <CategoryFilter
                                categories={categories}
                                activeCategory={activeCategory}
                                onCategoryChange={setActiveCategory}
                            />
                        </div>

                        {/* Product Grid */}
                        <div className="flex-1 overflow-y-auto p-3.5">
                            {!hasResults ? (
                                <div className="flex h-full items-center justify-center">
                                    <div className="text-center">
                                        <p className="text-sm text-muted-foreground">
                                            {searchQuery.trim()
                                                ? __('No products found')
                                                : __('No products available')}
                                        </p>
                                        {isSearching && (
                                            <p className="mt-2 text-xs text-muted-foreground">
                                                {__('Searching...')}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            ) : (
                                <div
                                    className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
                                    role="grid"
                                    aria-label={__('Available products')}
                                >
                                    {filteredProducts.map((product) => (
                                        <ProductCard
                                            key={product.id}
                                            {...product}
                                            onAddToCart={addToCart}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Cart Sidebar */}
                    <div className="relative flex w-full flex-col md:w-[28rem] lg:w-[32rem]">
                        {isLoadingCart && (
                            <div
                                className="absolute inset-0 z-10 flex items-center justify-center bg-background/50 backdrop-blur-sm"
                                role="status"
                                aria-live="polite"
                                aria-label={__('Updating cart')}
                            >
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <div className="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent" />
                                    <span>{__('Updating cart...')}</span>
                                </div>
                            </div>
                        )}
                        <CartSidebar
                            items={cart.items}
                            totals={cart.totals}
                            itemCount={itemCount}
                            onUpdateQuantity={updateQuantity}
                            onRemoveItem={removeItem}
                            onClearCart={clearCartAction}
                            onPayNow={handlePayNow}
                            onQuickPayExact={
                                settings.pos.default_payment_method !== 'cash'
                                    ? handleQuickPayExact
                                    : undefined
                            }
                            enableDiscounts={settings.sales.enable_discounts}
                            maxDiscountPercentage={
                                settings.sales.max_discount_percentage
                            }
                            onApplyDiscount={handleApplyDiscount}
                            onApplyTax={handleApplyTax}
                        />
                    </div>
                </div>

                {/* Keyboard Shortcuts Bar */}
                <KeyboardShortcutsBar />
            </div>

            {/* Modals */}
            <PaymentModal
                isOpen={modals.isPaymentModalOpen}
                onClose={modals.closePaymentModal}
                totalAmount={cart.totals.total}
                storeId={register?.store_id ?? 0}
                defaultPaymentMethod={settings.pos.default_payment_method}
                onSuccess={handlePaymentSuccess}
            />

            <RegisterSetupModal
                isOpen={modals.isRegisterModalOpen}
                onClose={modals.closeRegisterModal}
                stores={stores}
                moneyboxes={moneyboxes}
                currentRegister={register}
            />

            <QuickAddCustomerModal
                isOpen={modals.isQuickAddCustomerOpen}
                onClose={modals.closeQuickAddCustomer}
                onCustomerAdded={(customer) => {
                    selectCustomer(customer);
                    modals.closeQuickAddCustomer();
                }}
            />
        </>
    );
}
