import { useLanguage } from '@/hooks/use-language';
import type { Customer } from '@/types/pos';
import axios from 'axios';
import { useCallback, useState } from 'react';
import { toast } from 'sonner';

interface CreateCustomerData {
    name: string;
    email?: string;
    phone?: string;
}

interface CustomerSearchOptions {
    query?: string;
    limit?: number;
    activeOnly?: boolean;
}

/**
 * Custom hook for customer management
 *
 * Features:
 * - Search customers with debouncing
 * - Quick add customer
 * - Select/deselect customer
 * - Customer statistics
 * - Recent customers
 * - Validation
 *
 * @example
 * const {
 *   selectedCustomer,
 *   selectCustomer,
 *   clearCustomer,
 *   quickAddCustomer,
 *   searchCustomers,
 *   isLoading,
 * } = useCustomerManagement();
 */
export function useCustomerManagement() {
    const { __ } = useLanguage();
    const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(
        null,
    );
    const [customers, setCustomers] = useState<Customer[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [isSearching, setIsSearching] = useState(false);

    // Select customer
    const selectCustomer = useCallback(
        (customer: Customer) => {
            setSelectedCustomer(customer);
            toast.success(__('Customer selected'), {
                description: customer.name,
                duration: 2000,
            });
        },
        [__],
    );

    // Clear customer selection
    const clearCustomer = useCallback(() => {
        setSelectedCustomer(null);
        toast.info(__('Customer cleared'), {
            duration: 1500,
        });
    }, [__]);

    // Search customers
    const searchCustomers = useCallback(
        async (options: CustomerSearchOptions = {}) => {
            const { query = '', limit = 50, activeOnly = true } = options;

            setIsSearching(true);
            try {
                const response = await axios.get<{ data: Customer[] }>(
                    '/api/clients/search',
                    {
                        params: {
                            search: query,
                            limit,
                            is_active: activeOnly ? 1 : undefined,
                        },
                    },
                );

                setCustomers(response.data.data);
                return response.data.data;
            } catch (error) {
                console.error('Failed to search customers:', error);
                toast.error(__('Failed to search customers'));
                return [];
            } finally {
                setIsSearching(false);
            }
        },
        [__],
    );

    // Quick add customer (minimal info)
    const quickAddCustomer = useCallback(
        async (data: CreateCustomerData): Promise<Customer | null> => {
            setIsLoading(true);
            try {
                const response = await axios.post<{ data: Customer }>(
                    '/api/clients',
                    data,
                );
                const newCustomer = response.data.data;

                toast.success(__('Customer added successfully'), {
                    description: newCustomer.name,
                });

                // Auto-select the new customer
                setSelectedCustomer(newCustomer);

                return newCustomer;
            } catch (error: unknown) {
                console.error('Failed to add customer:', error);
                const errorMessage =
                    (error as { response?: { data?: { message?: string } } })
                        .response?.data?.message ||
                    __('Failed to add customer');
                toast.error(__('Failed to add customer'), {
                    description: errorMessage,
                });
                return null;
            } finally {
                setIsLoading(false);
            }
        },
        [__],
    );

    // Get customer by ID
    const getCustomerById = useCallback(
        async (customerId: number): Promise<Customer | null> => {
            setIsLoading(true);
            try {
                const response = await axios.get<{ data: Customer }>(
                    `/api/clients/${customerId}`,
                );
                return response.data.data;
            } catch (error) {
                console.error('Failed to get customer:', error);
                return null;
            } finally {
                setIsLoading(false);
            }
        },
        [],
    );

    // Update customer
    const updateCustomer = useCallback(
        async (
            customerId: number,
            data: Partial<CreateCustomerData>,
        ): Promise<Customer | null> => {
            setIsLoading(true);
            try {
                const response = await axios.patch<{ data: Customer }>(
                    `/api/clients/${customerId}`,
                    data,
                );
                const updatedCustomer = response.data.data;

                toast.success(__('Customer updated successfully'));

                // Update selected customer if it's the one being updated
                if (selectedCustomer?.id === customerId) {
                    setSelectedCustomer(updatedCustomer);
                }

                return updatedCustomer;
            } catch (error: unknown) {
                console.error('Failed to update customer:', error);
                const errorMessage =
                    (error as { response?: { data?: { message?: string } } })
                        .response?.data?.message ||
                    __('Failed to update customer');
                toast.error(__('Failed to update customer'), {
                    description: errorMessage,
                });
                return null;
            } finally {
                setIsLoading(false);
            }
        },
        [__, selectedCustomer],
    );

    return {
        // State
        selectedCustomer,
        customers,
        isLoading,
        isSearching,

        // Actions
        selectCustomer,
        clearCustomer,
        searchCustomers,
        quickAddCustomer,
        getCustomerById,
        updateCustomer,

        // Computed
        hasSelectedCustomer: selectedCustomer !== null,
    };
}

/**
 * Custom hook for customer validation
 *
 * @example
 * const { isValid, errors } = useCustomerValidation({
 *   name: 'John Doe',
 *   email: 'invalid-email',
 *   phone: '123',
 * });
 */
interface CustomerValidationData {
    name: string;
    email?: string;
    phone?: string;
}

interface ValidationResult {
    isValid: boolean;
    errors: Record<string, string[]>;
}

export function useCustomerValidation(
    data: CustomerValidationData,
): ValidationResult {
    const { __ } = useLanguage();
    const errors: Record<string, string[]> = {};

    // Validate name
    if (!data.name || data.name.trim().length === 0) {
        errors.name = [__('Name is required')];
    } else if (data.name.length < 2) {
        errors.name = [__('Name must be at least 2 characters')];
    } else if (data.name.length > 255) {
        errors.name = [__('Name must not exceed 255 characters')];
    }

    // Validate email (optional)
    if (data.email && data.email.trim().length > 0) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            errors.email = [__('Invalid email format')];
        }
    }

    // Validate phone (optional)
    if (data.phone && data.phone.trim().length > 0) {
        const phoneRegex = /^[0-9+\-\s()]+$/;
        if (!phoneRegex.test(data.phone)) {
            errors.phone = [__('Invalid phone format')];
        } else if (data.phone.replace(/\D/g, '').length < 7) {
            errors.phone = [__('Phone number must be at least 7 digits')];
        }
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors,
    };
}

/**
 * Custom hook for customer statistics and insights
 *
 * @example
 * const { topCustomers, recentCustomers, totalCustomers } = useCustomerInsights();
 */
interface CustomerInsights {
    topCustomers: Customer[];
    recentCustomers: Customer[];
    totalCustomers: number;
    activeCustomers: number;
    isLoading: boolean;
}

export function useCustomerInsights(): CustomerInsights {
    const [insights, setInsights] = useState<CustomerInsights>({
        topCustomers: [],
        recentCustomers: [],
        totalCustomers: 0,
        activeCustomers: 0,
        isLoading: false,
    });

    const loadInsights = useCallback(async () => {
        setInsights((prev) => ({ ...prev, isLoading: true }));

        try {
            // Fetch customer insights
            const response = await axios.get<{
                data: {
                    top_customers: Customer[];
                    recent_customers: Customer[];
                    total: number;
                    active: number;
                };
            }>('/api/clients/insights');

            setInsights({
                topCustomers: response.data.data.top_customers,
                recentCustomers: response.data.data.recent_customers,
                totalCustomers: response.data.data.total,
                activeCustomers: response.data.data.active,
                isLoading: false,
            });
        } catch (error) {
            console.error('Failed to load customer insights:', error);
            setInsights((prev) => ({ ...prev, isLoading: false }));
        }
    }, []);

    // Load insights on mount
    useState(() => {
        loadInsights();
    });

    return insights;
}

/**
 * Custom hook for customer loyalty/rewards
 *
 * @example
 * const { points, tier, canRedeem } = useCustomerLoyalty(customerId);
 */
interface CustomerLoyalty {
    points: number;
    tier: 'bronze' | 'silver' | 'gold' | 'platinum';
    canRedeem: boolean;
    redeemableValue: number;
}

export function useCustomerLoyalty(
    customerId: number | null,
): CustomerLoyalty | null {
    const [loyalty, setLoyalty] = useState<CustomerLoyalty | null>(null);

    const loadLoyalty = useCallback(async () => {
        if (!customerId) {
            setLoyalty(null);
            return;
        }

        try {
            const response = await axios.get<{ data: CustomerLoyalty }>(
                `/api/clients/${customerId}/loyalty`,
            );
            setLoyalty(response.data.data);
        } catch (error) {
            console.error('Failed to load customer loyalty:', error);
            setLoyalty(null);
        }
    }, [customerId]);

    useState(() => {
        loadLoyalty();
    });

    return loyalty;
}
