import { useLanguage } from '@/hooks/use-language';
import axios from 'axios';
import { useCallback, useState } from 'react';
import { toast } from 'sonner';

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

interface Product {
    id: number;
    name: string;
    price: number;
}

const EMPTY_CART: CartData = {
    items: [],
    totals: {
        subtotal: 0,
        discount_total: 0,
        tax_total: 0,
        total: 0,
    },
};

export function usePosCart(products: Product[]) {
    const { __ } = useLanguage();
    const [cart, setCart] = useState<CartData>(EMPTY_CART);
    const [isLoading, setIsLoading] = useState(false);

    const loadCart = useCallback(async () => {
        try {
            const response = await axios.get('/pos/cart');
            setCart(response.data.data);
        } catch (error) {
            console.error('Failed to load cart:', error);
            toast.error(__('Failed to load cart'), {
                description: __('Please refresh the page'),
            });
        }
    }, [__]);

    const clearCartState = useCallback(() => {
        setCart(EMPTY_CART);
    }, []);

    const addToCart = useCallback(
        async (productId: number) => {
            const product = products.find((p) => p.id === productId);
            if (!product) return;

            const tempLineId = `temp_${Date.now()}`;
            const optimisticItem: CartItem = {
                line_id: tempLineId,
                product_id: productId,
                name: product.name,
                unit_price: product.price,
                quantity: 1,
                line_subtotal: product.price,
            };

            // Optimistic update
            setCart((prev) => ({
                items: [...prev.items, optimisticItem],
                totals: {
                    ...prev.totals,
                    subtotal: prev.totals.subtotal + product.price,
                    total: prev.totals.total + product.price,
                },
            }));

            setIsLoading(true);
            try {
                const response = await axios.post('/pos/cart/items', {
                    product_id: productId,
                    quantity: 1,
                });
                setCart(response.data.data);
            } catch (error) {
                console.error('Failed to add to cart:', error);
                // Rollback
                setCart((prev) => ({
                    items: prev.items.filter(
                        (item) => item.line_id !== tempLineId,
                    ),
                    totals: {
                        ...prev.totals,
                        subtotal: prev.totals.subtotal - product.price,
                        total: prev.totals.total - product.price,
                    },
                }));
                toast.error(__('Failed to add product to cart'), {
                    description: __('Please try again'),
                });
            } finally {
                setIsLoading(false);
            }
        },
        [products, __],
    );

    const updateQuantity = useCallback(
        async (lineId: string, quantity: number) => {
            const previousCart = { ...cart };

            // Optimistic update
            setCart((prev) => {
                const updatedItems = prev.items.map((item) => {
                    if (item.line_id === lineId) {
                        const newSubtotal = item.unit_price * quantity;
                        return {
                            ...item,
                            quantity,
                            line_subtotal: newSubtotal,
                        };
                    }
                    return item;
                });

                const newSubtotal = updatedItems.reduce(
                    (sum, item) => sum + item.line_subtotal,
                    0,
                );

                return {
                    items: updatedItems,
                    totals: {
                        ...prev.totals,
                        subtotal: newSubtotal,
                        total:
                            newSubtotal +
                            prev.totals.tax_total -
                            prev.totals.discount_total,
                    },
                };
            });

            setIsLoading(true);
            try {
                const response = await axios.patch(
                    `/pos/cart/items/${lineId}`,
                    {
                        quantity,
                    },
                );
                setCart(response.data.data);
            } catch (error) {
                console.error('Failed to update quantity:', error);
                setCart(previousCart);
                toast.error(__('Failed to update quantity'), {
                    description: __('Please try again'),
                });
            } finally {
                setIsLoading(false);
            }
        },
        [cart, __],
    );

    const removeItem = useCallback(
        async (lineId: string) => {
            const previousCart = { ...cart };
            const itemToRemove = cart.items.find(
                (item) => item.line_id === lineId,
            );
            if (!itemToRemove) return;

            // Optimistic removal
            setCart((prev) => {
                const updatedItems = prev.items.filter(
                    (item) => item.line_id !== lineId,
                );
                const newSubtotal = updatedItems.reduce(
                    (sum, item) => sum + item.line_subtotal,
                    0,
                );

                return {
                    items: updatedItems,
                    totals: {
                        ...prev.totals,
                        subtotal: newSubtotal,
                        total:
                            newSubtotal +
                            prev.totals.tax_total -
                            prev.totals.discount_total,
                    },
                };
            });

            setIsLoading(true);
            try {
                const response = await axios.delete(
                    `/pos/cart/items/${lineId}`,
                );
                setCart(response.data.data);
            } catch (error) {
                console.error('Failed to remove item:', error);
                setCart(previousCart);
                toast.error(__('Failed to remove item'), {
                    description: __('Please try again'),
                });
            } finally {
                setIsLoading(false);
            }
        },
        [cart, __],
    );

    const clearCart = useCallback(async () => {
        if (!confirm(__('Are you sure you want to clear the cart?'))) {
            return;
        }

        setIsLoading(true);
        try {
            await axios.delete('/pos/cart');
            clearCartState();
            toast.success(__('Cart cleared'));
        } catch (error) {
            console.error('Failed to clear cart:', error);
            toast.error(__('Failed to clear cart'));
        } finally {
            setIsLoading(false);
        }
    }, [__, clearCartState]);

    return {
        cart,
        isLoading,
        loadCart,
        addToCart,
        updateQuantity,
        removeItem,
        clearCart,
        clearCartState,
    };
}
