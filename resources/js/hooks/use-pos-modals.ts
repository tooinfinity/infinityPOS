import { useCallback, useState } from 'react';

export function usePosModals() {
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [isRegisterModalOpen, setIsRegisterModalOpen] = useState(false);
    const [isDiscountModalOpen, setIsDiscountModalOpen] = useState(false);
    const [isQuickAddCustomerOpen, setIsQuickAddCustomerOpen] = useState(false);

    const openPaymentModal = useCallback(() => setIsPaymentModalOpen(true), []);
    const closePaymentModal = useCallback(
        () => setIsPaymentModalOpen(false),
        [],
    );

    const openRegisterModal = useCallback(
        () => setIsRegisterModalOpen(true),
        [],
    );
    const closeRegisterModal = useCallback(
        () => setIsRegisterModalOpen(false),
        [],
    );

    const openDiscountModal = useCallback(
        () => setIsDiscountModalOpen(true),
        [],
    );
    const closeDiscountModal = useCallback(
        () => setIsDiscountModalOpen(false),
        [],
    );

    const openQuickAddCustomer = useCallback(
        () => setIsQuickAddCustomerOpen(true),
        [],
    );
    const closeQuickAddCustomer = useCallback(
        () => setIsQuickAddCustomerOpen(false),
        [],
    );

    return {
        // Payment Modal
        isPaymentModalOpen,
        openPaymentModal,
        closePaymentModal,

        // Register Modal
        isRegisterModalOpen,
        openRegisterModal,
        closeRegisterModal,

        // Discount Modal
        isDiscountModalOpen,
        openDiscountModal,
        closeDiscountModal,

        // Quick Add Customer
        isQuickAddCustomerOpen,
        openQuickAddCustomer,
        closeQuickAddCustomer,
    };
}
