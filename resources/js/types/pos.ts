/**
 * Centralized POS Type Definitions
 *
 * This file contains all shared types used across the POS system.
 * Types are organized by domain for better maintainability.
 */

// ============================================================================
// Product Types
// ============================================================================

export interface Product {
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
    tax?: {
        id: number;
        name: string;
        rate: number;
    } | null;
}

export interface Category {
    id: number;
    name: string;
    code: string;
}

// ============================================================================
// Cart Types
// ============================================================================

export interface CartItem {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
    discount: number;
    tax_amount: number;
    line_total: number;
}

export interface CartTotals {
    subtotal: number;
    tax_total: number;
    discount_total: number;
    total: number;
}

export interface CartDiscount {
    type: 'percentage' | 'fixed';
    value: number;
    reason?: string;
}

// ============================================================================
// Customer Types
// ============================================================================

export interface Customer {
    id: number;
    name: string;
    phone?: string | null;
    email?: string | null;
}

// ============================================================================
// Register Types
// ============================================================================

export interface Register {
    id: number;
    name: string;
    store_id: number;
    moneybox_id: number | null;
}

export interface Store {
    id: number;
    name: string;
}

export interface Moneybox {
    id: number;
    name: string;
    store_id?: number;
}

// ============================================================================
// Payment Types
// ============================================================================

export type PaymentMethod = 'cash' | 'card' | 'transfer';

export interface PaymentData {
    store_id: number;
    method: PaymentMethod;
    amount: number;
    customer_id?: number;
}

export interface PaymentResult {
    sale_id: number;
    sale_reference: string;
    amount_paid: number;
    change_due: number;
    receipt: ReceiptData;
}

// ============================================================================
// Receipt Types
// ============================================================================

export interface ReceiptData {
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
    payment_method: string;
    amount_received: number;
    change_due: number;
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

// ============================================================================
// Settings Types
// ============================================================================

export interface PosSettings {
    enable_barcode_scanner: boolean;
    default_payment_method: string;
    auto_print_receipt: boolean;
}

export interface SalesSettings {
    enable_discounts: boolean;
    max_discount_percentage: number;
    require_customer_for_sale: boolean;
    enable_tax_calculation: boolean;
}

export interface Settings {
    pos: PosSettings;
    sales: SalesSettings;
}

// ============================================================================
// API Response Types
// ============================================================================

export interface ProductSearchResponse {
    data: Product[];
}

// ============================================================================
// Component Props Types
// ============================================================================

export interface PosIndexProps {
    products: Product[];
    categories: Category[];
    register: Register | null;
    settings: Settings;
    stores?: Store[];
    moneyboxes?: Moneybox[];
}
