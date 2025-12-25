import { CartSidebar } from '@/components/pos/cart-sidebar';
import { CategoryFilter } from '@/components/pos/category-filter';
import {
    CustomerSelector,
    QuickAddCustomerModal,
} from '@/components/pos/customer-selector';
import { DiscountModal } from '@/components/pos/discount-modal';
import {
    HeldSalesListModal,
    HoldSaleModal,
} from '@/components/pos/hold-sale-modal';
import { KeyboardShortcutsBar } from '@/components/pos/keyboard-shortcuts-bar';
import { PaymentModal } from '@/components/pos/payment-modal';
import { ProductCard } from '@/components/pos/product-card';
import { ReceiptModal } from '@/components/pos/receipt-modal';
import { RegisterSetupModal } from '@/components/pos/register-setup-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useLanguage } from '@/hooks/use-language';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import {
    ArrowLeft,
    Clock,
    Percent,
    Search,
    Settings,
    UserPlus,
} from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

interface Category {
    id: number;
    name: string;
    code: string;
}

interface Product {
    id: number;
    name: string;
    sku: string | null;
    barcode: string | null;
    price: number;
    image: string | null;
    available_stock: number | null;
    category?: {
        id: number;
        name: string;
    } | null;
}

interface Customer {
    id: number;
    name: string;
    phone?: string | null;
    email?: string | null;
}

interface CartItem {
    line_id: string;
    product_id: number;
    name: string;
    unit_price: number;
    quantity: number;
    line_subtotal: number;
}

interface CartTotals {
    subtotal: number;
    discount_total: number;
    tax_total: number;
    total: number;
}

interface CartData {
    items: CartItem[];
    totals: CartTotals;
}

interface Register {
    id: number;
    name: string;
    store_id: number;
    moneybox_id: number | null;
}

interface Store {
    id: number;
    name: string;
}

interface Moneybox {
    id: number;
    name: string;
}

interface Settings {
    pos: {
        enable_barcode_scanner: boolean;
        default_payment_method: string;
        auto_print_receipt: boolean;
    };
    sales: {
        enable_discounts: boolean;
        max_discount_percentage: number;
        require_customer_for_sale: boolean;
        enable_tax_calculation: boolean;
    };
}

interface ReceiptData {
    sale_reference: string;
    date: string;
    items: Array<{
        name: string;
        quantity: number;
        unit_price: number;
        line_subtotal: number;
    }>;
    subtotal: number;
    tax_total: number;
    discount_total: number;
    total: number;
    amount_received: number;
    change_due: number;
    payment_method: string;
    customer?: {
        name: string;
        phone?: string;
    } | null;
    store?: {
        name: string;
        address?: string;
        phone?: string;
    } | null;
}

interface Props {
    products: Product[];
    categories: Category[];
    register: Register | null;
    settings: Settings;
    stores?: Store[];
    moneyboxes?: Moneybox[];
}

export default function PosIndex({
    products,
    categories,
    register,
    settings,
    stores = [],
    moneyboxes = [],
}: Props) {
    const { __ } = useLanguage();
    const [activeCategory, setActiveCategory] = useState<number | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [cart, setCart] = useState<CartData>({
        items: [],
        totals: {
            subtotal: 0,
            discount_total: 0,
            tax_total: 0,
            total: 0,
        },
    });
    // Track loading state for cart operations
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const [isLoadingCart, setIsLoadingCart] = useState(false);
    const searchInputRef = useRef<HTMLInputElement>(null);

    // Customer state
    const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(
        null,
    );

    // Modal states
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [isHoldSaleModalOpen, setIsHoldSaleModalOpen] = useState(false);
    const [isHeldSalesListOpen, setIsHeldSalesListOpen] = useState(false);
    const [isRegisterModalOpen, setIsRegisterModalOpen] = useState(false);
    const [isDiscountModalOpen, setIsDiscountModalOpen] = useState(false);
    const [isQuickAddCustomerOpen, setIsQuickAddCustomerOpen] = useState(false);
    const [isReceiptModalOpen, setIsReceiptModalOpen] = useState(false);
    const [receiptData, setReceiptData] = useState<ReceiptData | null>(null);

    // Load cart on mount
    useEffect(() => {
        loadCart();
    }, []);

    const loadCart = async () => {
        try {
            const response = await axios.get('/pos/cart');
            setCart(response.data.data);
        } catch (error) {
            console.error('Failed to load cart:', error);
        }
    };

    const handleAddToCart = async (productId: number) => {
        setIsLoadingCart(true);
        try {
            const response = await axios.post('/pos/cart/items', {
                product_id: productId,
                quantity: 1,
            });
            setCart(response.data.data);
        } catch (error) {
            console.error('Failed to add to cart:', error);
        } finally {
            setIsLoadingCart(false);
        }
    };

    const handleUpdateQuantity = async (lineId: string, quantity: number) => {
        setIsLoadingCart(true);
        try {
            const response = await axios.patch(`/pos/cart/items/${lineId}`, {
                quantity,
            });
            setCart(response.data.data);
        } catch (error) {
            console.error('Failed to update quantity:', error);
        } finally {
            setIsLoadingCart(false);
        }
    };

    const handleRemoveItem = async (lineId: string) => {
        setIsLoadingCart(true);
        try {
            const response = await axios.delete(`/pos/cart/items/${lineId}`);
            setCart(response.data.data);
        } catch (error) {
            console.error('Failed to remove item:', error);
        } finally {
            setIsLoadingCart(false);
        }
    };

    const handleClearCart = useCallback(async () => {
        if (!confirm(__('Are you sure you want to clear the cart?'))) {
            return;
        }

        setIsLoadingCart(true);
        try {
            await axios.delete('/pos/cart');
            setCart({
                items: [],
                totals: {
                    subtotal: 0,
                    discount_total: 0,
                    tax_total: 0,
                    total: 0,
                },
            });
        } catch (error) {
            console.error('Failed to clear cart:', error);
        } finally {
            setIsLoadingCart(false);
        }
    }, [__]);

    const handleHoldSale = useCallback(() => {
        if (cart.items.length === 0) return;
        setIsHoldSaleModalOpen(true);
    }, [cart.items.length]);

    const handlePayNow = useCallback(() => {
        if (cart.items.length === 0) return;
        setIsPaymentModalOpen(true);
    }, [cart.items.length]);

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            // Don't trigger shortcuts when typing in input fields
            if (
                e.target instanceof HTMLInputElement ||
                e.target instanceof HTMLTextAreaElement
            ) {
                return;
            }

            switch (e.key) {
                case 'Enter':
                    e.preventDefault();
                    if (cart.items.length > 0) {
                        handlePayNow();
                    }
                    break;
                case 'F2':
                    e.preventDefault();
                    searchInputRef.current?.focus();
                    break;
                case 'F4':
                    e.preventDefault();
                    if (cart.items.length > 0) {
                        handleHoldSale();
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    if (cart.items.length > 0) {
                        handleClearCart();
                    }
                    break;
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [cart.items.length, handlePayNow, handleHoldSale, handleClearCart]);

    const handlePaymentSuccess = (receipt: ReceiptData) => {
        // Show receipt modal
        setReceiptData(receipt);
        setIsReceiptModalOpen(true);

        // Clear cart and customer
        setCart({
            items: [],
            totals: {
                subtotal: 0,
                discount_total: 0,
                tax_total: 0,
                total: 0,
            },
        });
        setSelectedCustomer(null);

        // Auto-print if enabled
        if (settings.pos.auto_print_receipt && receipt) {
            setTimeout(() => {
                // The receipt modal will handle printing
            }, 500);
        }
    };

    const handleHoldSaleSuccess = () => {
        // Clear cart after holding
        handleClearCart();
    };

    // Filter products
    const filteredProducts = useMemo(() => {
        let filtered = products;

        // Filter by category
        if (activeCategory !== null) {
            filtered = filtered.filter(
                (p) => p.category?.id === activeCategory,
            );
        }

        // Filter by search query
        if (searchQuery.trim() !== '') {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter(
                (p) =>
                    p.name.toLowerCase().includes(query) ||
                    p.sku?.toLowerCase().includes(query) ||
                    p.barcode?.toLowerCase().includes(query),
            );
        }

        return filtered;
    }, [products, activeCategory, searchQuery]);

    const itemCount = cart.items.reduce((sum, item) => sum + item.quantity, 0);

    return (
        <>
            <Head title={__('Point of Sale')} />

            <div className="flex h-screen flex-col bg-background">
                {/* Header */}
                <header className="flex items-center justify-between border-b border-border bg-card px-4 py-3">
                    <div className="flex items-center gap-4">
                        {/* Back to Dashboard */}
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => router.visit('/dashboard')}
                            className="gap-2"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            {__('Dashboard')}
                        </Button>

                        <div className="h-6 w-px bg-border" />

                        <div className="flex items-center gap-2">
                            <div className="rounded-lg bg-primary px-3 py-2 text-sm font-bold text-primary-foreground">
                                POS
                            </div>
                            <div className="text-sm">
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
                            size="sm"
                            onClick={() => setIsHeldSalesListOpen(true)}
                            className="gap-2"
                        >
                            <Clock className="h-4 w-4" />
                            {__('Held Sales')}
                        </Button>

                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setIsRegisterModalOpen(true)}
                            title={__('Register settings')}
                        >
                            <Settings className="h-5 w-5" />
                        </Button>
                    </div>
                </header>

                {/* Main Content */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Products Section */}
                    <div className="flex flex-1 flex-col overflow-hidden">
                        {/* Customer & Category Bar */}
                        <div className="space-y-3 border-b border-border bg-card px-4 py-3">
                            {/* Customer Selection */}
                            <div className="flex items-center gap-2">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground gap-2" />
                                <Input
                                    ref={searchInputRef}
                                    type="text"
                                    placeholder={__(
                                        'Search products or scan barcode...',
                                    )}
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="h-10 pl-10 gap-6"
                                />
                                <CustomerSelector
                                    selectedCustomer={selectedCustomer}
                                    onCustomerChange={setSelectedCustomer}
                                    required={
                                        settings.sales.require_customer_for_sale
                                    }
                                />
                                <Button
                                    variant="outline"
                                    size="icon"
                                    onClick={() =>
                                        setIsQuickAddCustomerOpen(true)
                                    }
                                    title={__('Quick add customer')}
                                >
                                    <UserPlus className="h-4 w-4" />
                                </Button>
                                {settings.sales.enable_discounts && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            setIsDiscountModalOpen(true)
                                        }
                                        disabled={cart.items.length === 0}
                                        className="ml-auto gap-2"
                                    >
                                        <Percent className="h-4 w-4" />
                                        {__('Discount')}
                                    </Button>
                                )}
                            </div>

                            {/* Category Filter */}
                            <CategoryFilter
                                categories={categories}
                                activeCategory={activeCategory}
                                onCategoryChange={setActiveCategory}
                            />
                        </div>

                        {/* Product Grid */}
                        <div className="flex-1 overflow-y-auto p-4">
                            {filteredProducts.length === 0 ? (
                                <div className="flex h-full items-center justify-center">
                                    <div className="text-center">
                                        <p className="text-muted-foreground">
                                            {searchQuery
                                                ? __('No products found')
                                                : __('No products available')}
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                                    {filteredProducts.map((product) => (
                                        <ProductCard
                                            key={product.id}
                                            {...product}
                                            onAddToCart={handleAddToCart}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Cart Sidebar */}
                    <div className="flex w-96 flex-col">
                        <CartSidebar
                            items={cart.items}
                            totals={cart.totals}
                            itemCount={itemCount}
                            onUpdateQuantity={handleUpdateQuantity}
                            onRemoveItem={handleRemoveItem}
                            onClearCart={handleClearCart}
                            onHoldSale={handleHoldSale}
                            onPayNow={handlePayNow}
                            enableDiscounts={settings.sales.enable_discounts}
                        />
                    </div>
                </div>

                {/* Keyboard Shortcuts Bar */}
                <KeyboardShortcutsBar />
            </div>

            {/* Modals */}
            <PaymentModal
                isOpen={isPaymentModalOpen}
                onClose={() => setIsPaymentModalOpen(false)}
                totalAmount={cart.totals.total}
                storeId={register?.store_id ?? 0}
                defaultPaymentMethod={settings.pos.default_payment_method}
                onSuccess={handlePaymentSuccess}
            />

            <HoldSaleModal
                isOpen={isHoldSaleModalOpen}
                onClose={() => setIsHoldSaleModalOpen(false)}
                onSuccess={handleHoldSaleSuccess}
            />

            <HeldSalesListModal
                isOpen={isHeldSalesListOpen}
                onClose={() => setIsHeldSalesListOpen(false)}
                onResume={(saleId) => {
                    console.log('Resume sale:', saleId);
                    // TODO: Load held sale into cart
                }}
            />

            <RegisterSetupModal
                isOpen={isRegisterModalOpen}
                onClose={() => setIsRegisterModalOpen(false)}
                stores={stores}
                moneyboxes={moneyboxes}
                currentRegister={register}
            />

            <DiscountModal
                isOpen={isDiscountModalOpen}
                onClose={() => setIsDiscountModalOpen(false)}
                currentSubtotal={cart.totals.subtotal}
                currentDiscount={cart.totals.discount_total}
                maxDiscountPercentage={settings.sales.max_discount_percentage}
                onDiscountApplied={loadCart}
            />

            <QuickAddCustomerModal
                isOpen={isQuickAddCustomerOpen}
                onClose={() => setIsQuickAddCustomerOpen(false)}
                onCustomerAdded={(customer) => {
                    setSelectedCustomer(customer);
                    setIsQuickAddCustomerOpen(false);
                }}
            />

            <ReceiptModal
                isOpen={isReceiptModalOpen}
                onClose={() => setIsReceiptModalOpen(false)}
                receipt={receiptData}
                autoPrint={settings.pos.auto_print_receipt}
            />
        </>
    );
}
