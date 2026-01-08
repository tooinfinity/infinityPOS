# TASK: Implement Complete POS & Inventory Management Schema for Laravel 12

## 🎯 PROJECT CONTEXT
You are implementing a Point of Sale (POS) and Inventory Management system for small local stores using:
- **Laravel 12**
- **Actions Pattern** (single responsibility classes)
- **Jobs** for background/queued tasks
- **Query Classes** for complex database queries
- **Collections** for data transformation
- **FIFO (First-In-First-Out)** inventory costing
- **Integer-only** storage (no decimals - all amounts in cents)
- **Multi-store** support (future-ready)
- **Cash register** management with sessions
- **Invoice system** for B2B customers
- **Spatie Laravel Permission** for roles

## 🏗️ ARCHITECTURE PRINCIPLES

### ✅ WHAT WE USE:
1. **ACTIONS** - Single-purpose classes that do ONE thing
    - Example: `CreatePurchaseAction`, `ProcessSaleAction`, `AdjustStockAction`
    - Located in: `app/Actions/`
    - Each action has ONE public method: `execute()` or `handle()`

2. **JOBS** - For queued/background tasks
    - Example: `ProcessLowStockAlertsJob`, `GenerateDailyReportJob`
    - Located in: `app/Jobs/`
    - Use when task can be async or takes time

3. **QUERY CLASSES** - For complex database queries
    - Example: `GetBestSellingProductsQuery`, `CalculateProfitQuery`
    - Located in: `app/Queries/`
    - Return query builder or collection

4. **COLLECTIONS** - For data transformation
    - Custom collection methods for domain logic
    - Example: `ProductCollection`, `SaleCollection`
    - Located in: `app/Collections/`

5. **DATA TRANSFER OBJECTS (DTOs)** - For type-safe data passing
    - Example: `SaleData`, `PurchaseData`
    - Located in: `app/DataTransferObjects/` or `app/DTOs/`

### ❌ WHAT WE DON'T USE:
1. **NO SERVICES** - Services are ONLY for third-party APIs (Stripe, AWS, etc.)
    - Don't create: `SaleService`, `PurchaseService`, etc.
    - Use Actions instead

2. **NO REPOSITORIES** - Use Eloquent directly
    - Models have relationships and scopes
    - Query classes for complex queries

3. **NO EVENTS** - Keep it simple, no event-driven architecture
    - Actions call other actions directly if needed
    - Use Jobs for async tasks

4. **NO OBSERVERS** - Logic should be explicit in actions
    - Don't hide behavior in model observers

## 🚨 CRITICAL CONSTRAINTS - READ CAREFULLY

### ABSOLUTE RULES (NEVER VIOLATE):
1. ❌ **NO DECIMALS** - All monetary values MUST be integers (cents)
2. ❌ **NO TAX CALCULATIONS** - Tax is not used in this system
3. ✅ **FIFO ONLY** - Use FIFO costing via `inventory_batches` table
4. ✅ **INTEGER QUANTITIES** - Except for weight/volume (stored as grams/milliliters)
5. ✅ **PROPER INDEXING** - Add indexes exactly as specified
6. ✅ **CASCADE DELETES** - Use `onDelete('cascade')` where specified
7. ✅ **ACTIONS PATTERN** - Single responsibility, one public method
8. ✅ **NO SERVICES** - Except for third-party integrations

### MONETARY VALUE CONVERSION:
```php
// Always store money as integers (cents)
$10.50 → 1050 (cents)
$100.00 → 10000 (cents)
$0.99 → 99 (cents)

// Helper functions needed:
moneyToCents(10.50) → 1050
centsToMoney(1050) → 10.50
formatMoney(1050) → "$10.50"
```

### QUANTITY STORAGE:
```php
// Pieces: store as-is
5 pieces → 5

// Weight: store in grams
2.5 kg → 2500 (grams)

// Volume: store in milliliters  
1.5 L → 1500 (milliliters)
```

## 📊 COMPLETE SCHEMA TO IMPLEMENT

### DATABASE TABLES (35 Total)

[SAME SCHEMA AS BEFORE - I'll include the full schema here]

#### 1. AUTHENTICATION & AUTHORIZATION
```
users (Laravel default + modifications)
├── id (PK)
├── name (varchar 255)
├── email (varchar 255, unique)
├── password (varchar 255)
├── store_id (FK -> stores, nullable)
├── is_active (boolean, default true)
└── timestamps

stores
├── id (PK)
├── name (varchar 255)
├── address (text, nullable)
├── phone (varchar 20, nullable)
├── currency (varchar 3, default 'USD')
└── timestamps
```

#### 2. PRODUCT CATALOG
```
categories
├── id (PK)
├── name (varchar 100)
├── description (text, nullable)
└── timestamps

products
├── id (PK)
├── category_id (FK -> categories, nullable, nullOnDelete)
├── name (varchar 255)
├── sku (varchar 50, unique, indexed)
├── barcode (varchar 100, unique, indexed, nullable)
├── description (text, nullable)
├── unit (enum: 'piece', 'gram', 'milliliter', default 'piece')
├── selling_price (integer) -- in cents
├── alert_quantity (integer, default 10)
├── image (varchar 255, nullable)
├── is_active (boolean, default true)
└── timestamps

INDEXES: barcode, sku
```

#### 3. INVENTORY (FIFO SYSTEM)
```
inventory
├── id (PK)
├── store_id (FK -> stores, cascadeOnDelete)
├── product_id (FK -> products, cascadeOnDelete)
├── total_quantity (integer, default 0)
└── updated_at

UNIQUE KEY: (store_id, product_id)
INDEX: (store_id, product_id)

inventory_batches (⭐ CRITICAL FOR FIFO)
├── id (PK)
├── store_id (FK -> stores, cascadeOnDelete)
├── product_id (FK -> products, cascadeOnDelete)
├── purchase_item_id (FK -> purchase_items)
├── quantity_received (integer)
├── quantity_remaining (integer)
├── unit_cost (integer) -- in cents
├── batch_date (datetime)
└── timestamps

INDEX: (store_id, product_id, batch_date)
```

#### 4. SUPPLIERS & PURCHASING
```
suppliers
├── id (PK)
├── name (varchar 255)
├── contact_person (varchar 100, nullable)
├── phone (varchar 20, nullable)
├── email (varchar 100, nullable)
├── address (text, nullable)
└── timestamps

purchases
├── id (PK)
├── store_id (FK -> stores)
├── supplier_id (FK -> suppliers, nullable)
├── reference_number (varchar 50, unique, indexed)
├── invoice_number (varchar 100, nullable)
├── purchase_date (date)
├── total_cost (integer) -- in cents
├── paid_amount (integer, default 0) -- in cents
├── payment_status (enum: 'pending', 'partial', 'paid', default 'pending')
├── payment_method (enum: 'cash', 'card', 'bank_transfer', nullable)
├── notes (text, nullable)
├── created_by (FK -> users)
└── timestamps

INDEXES: reference_number, purchase_date, invoice_number

purchase_items
├── id (PK)
├── purchase_id (FK -> purchases, cascadeOnDelete)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_cost (integer) -- in cents
├── subtotal (integer) -- in cents
└── timestamps
```

#### 5. CUSTOMERS
```
customers
├── id (PK)
├── name (varchar 255)
├── phone (varchar 20, unique, nullable)
├── email (varchar 100, unique, nullable)
├── address (text, nullable)
├── customer_type (enum: 'walk-in', 'regular', 'business', default 'walk-in')
└── timestamps

INDEX: phone
```

#### 6. CASH REGISTERS
```
cash_registers
├── id (PK)
├── store_id (FK -> stores)
├── name (varchar 100)
├── description (text, nullable)
├── is_active (boolean, default true)
└── timestamps

register_sessions
├── id (PK)
├── cash_register_id (FK -> cash_registers)
├── opened_by (FK -> users)
├── closed_by (FK -> users, nullable)
├── opening_time (datetime)
├── closing_time (datetime, nullable)
├── opening_balance (integer) -- in cents
├── expected_cash (integer, nullable) -- in cents
├── actual_cash (integer, nullable) -- in cents
├── difference (integer, nullable) -- in cents
├── notes (text, nullable)
├── status (enum: 'open', 'closed', default 'open')
└── timestamps

INDEX: (cash_register_id, status)
```

#### 7. SALES
```
sales
├── id (PK)
├── store_id (FK -> stores)
├── customer_id (FK -> customers, nullable, nullOnDelete)
├── register_session_id (FK -> register_sessions, nullable)
├── invoice_number (varchar 50, unique, indexed)
├── sale_date (datetime)
├── subtotal (integer) -- in cents
├── discount_amount (integer, default 0) -- in cents
├── total_amount (integer) -- in cents
├── payment_method (enum: 'cash', 'card', 'split')
├── amount_paid (integer) -- in cents
├── change_given (integer, default 0) -- in cents
├── status (enum: 'completed', 'pending', 'returned', default 'completed')
├── notes (text, nullable)
├── cashier_id (FK -> users)
└── timestamps

INDEXES: invoice_number, sale_date, (store_id, sale_date)

sale_items
├── id (PK)
├── sale_id (FK -> sales, cascadeOnDelete)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_price (integer) -- in cents
├── unit_cost (integer) -- in cents (from FIFO)
├── subtotal (integer) -- in cents
├── profit (integer) -- in cents
└── timestamps

INDEX: product_id

sale_item_batches (⭐ TRACKS WHICH BATCHES USED)
├── id (PK)
├── sale_item_id (FK -> sale_items, cascadeOnDelete)
├── inventory_batch_id (FK -> inventory_batches)
├── quantity_used (integer)
├── unit_cost (integer) -- in cents
└── created_at

sale_payments
├── id (PK)
├── sale_id (FK -> sales, cascadeOnDelete)
├── payment_method (enum: 'cash', 'card')
├── amount (integer) -- in cents
├── reference_number (varchar 100, nullable)
└── created_at
```

#### 8. INVOICES (B2B)
```
invoices
├── id (PK)
├── store_id (FK -> stores)
├── customer_id (FK -> customers)
├── invoice_number (varchar 50, unique, indexed)
├── invoice_date (date)
├── due_date (date, nullable)
├── subtotal (integer) -- in cents
├── discount_amount (integer, default 0) -- in cents
├── total_amount (integer) -- in cents
├── paid_amount (integer, default 0) -- in cents
├── payment_status (enum: 'unpaid', 'partial', 'paid', 'overdue', default 'unpaid')
├── notes (text, nullable)
├── terms (text, nullable)
├── created_by (FK -> users)
└── timestamps

INDEXES: invoice_number, invoice_date, due_date

invoice_items
├── id (PK)
├── invoice_id (FK -> invoices, cascadeOnDelete)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_price (integer) -- in cents
├── unit_cost (integer) -- in cents (from FIFO)
├── subtotal (integer) -- in cents
├── profit (integer) -- in cents
└── timestamps

invoice_payments
├── id (PK)
├── invoice_id (FK -> invoices)
├── payment_date (date)
├── amount (integer) -- in cents
├── payment_method (enum: 'cash', 'card', 'bank_transfer', 'check')
├── reference_number (varchar 100, nullable)
├── notes (text, nullable)
├── recorded_by (FK -> users)
└── timestamps
```

#### 9. RETURNS
```
returns
├── id (PK)
├── sale_id (FK -> sales, nullable)
├── invoice_id (FK -> invoices, nullable)
├── store_id (FK -> stores)
├── customer_id (FK -> customers, nullable)
├── return_number (varchar 50, unique)
├── return_date (datetime)
├── total_amount (integer) -- in cents
├── refund_method (enum: 'cash', 'card', 'store_credit')
├── reason (text, nullable)
├── processed_by (FK -> users)
└── timestamps

return_items
├── id (PK)
├── return_id (FK -> returns, cascadeOnDelete)
├── sale_item_id (FK -> sale_items, nullable)
├── invoice_item_id (FK -> invoice_items, nullable)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_price (integer) -- in cents
├── unit_cost (integer) -- in cents
├── subtotal (integer) -- in cents
└── timestamps
```

#### 10. STOCK ADJUSTMENTS
```
stock_adjustments
├── id (PK)
├── store_id (FK -> stores)
├── product_id (FK -> products)
├── adjustment_type (enum: 'expired', 'damaged', 'manual', 'correction')
├── quantity (integer) -- negative for removal
├── unit_cost (integer, nullable) -- in cents
├── total_cost (integer, nullable) -- in cents
├── reason (text)
├── adjusted_by (FK -> users)
└── created_at

INDEX: (store_id, created_at)
```

#### 11. CASH TRANSACTIONS
```
cash_transactions
├── id (PK)
├── register_session_id (FK -> register_sessions)
├── transaction_type (enum: 'sale', 'expense', 'withdrawal', 'deposit', 'opening', 'closing')
├── amount (integer) -- in cents (can be negative)
├── reference_type (varchar 255, nullable) -- polymorphic
├── reference_id (bigint unsigned, nullable) -- polymorphic
├── description (text, nullable)
├── created_by (FK -> users)
└── created_at

INDEX: (register_session_id, created_at)

expenses
├── id (PK)
├── store_id (FK -> stores)
├── register_session_id (FK -> register_sessions, nullable)
├── expense_category (enum: 'utilities', 'supplies', 'maintenance', 'other')
├── amount (integer) -- in cents
├── description (text)
├── expense_date (date)
├── recorded_by (FK -> users)
└── timestamps
```

## 🔧 IMPLEMENTATION STEPS

### STEP 1: Setup & Dependencies
```bash
# Install Spatie Permission
composer require spatie/laravel-permission

# Publish migration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### STEP 2: Create Directory Structure
```
app/
├── Actions/
│   ├── Purchase/
│   │   ├── CreatePurchaseAction.php
│   │   ├── AddPurchaseItemAction.php
│   │   └── CreateInventoryBatchAction.php
│   ├── Sale/
│   │   ├── ProcessSaleAction.php
│   │   ├── AddSaleItemAction.php
│   │   ├── DeductInventoryFifoAction.php
│   │   └── RecordSalePaymentAction.php
│   ├── Inventory/
│   │   ├── AdjustStockAction.php
│   │   ├── UpdateInventorySummaryAction.php
│   │   └── CheckLowStockAction.php
│   ├── CashRegister/
│   │   ├── OpenRegisterSessionAction.php
│   │   ├── CloseRegisterSessionAction.php
│   │   └── RecordCashTransactionAction.php
│   ├── Invoice/
│   │   ├── CreateInvoiceAction.php
│   │   ├── RecordInvoicePaymentAction.php
│   │   └── UpdateInvoiceStatusAction.php
│   └── Return/
│       ├── ProcessReturnAction.php
│       └── RestoreInventoryAction.php
├── Collections/
│   ├── ProductCollection.php
│   ├── SaleCollection.php
│   └── InventoryBatchCollection.php
├── DTOs/
│   ├── PurchaseData.php
│   ├── SaleData.php
│   ├── SaleItemData.php
│   └── PaymentData.php
├── Jobs/
│   ├── CheckLowStockAlertsJob.php
│   ├── GenerateDailyReportJob.php
│   └── MarkOverdueInvoicesJob.php
├── Queries/
│   ├── GetBestSellingProductsQuery.php
│   ├── CalculateProfitQuery.php
│   ├── GetInventoryValuationQuery.php
│   └── GetLowStockProductsQuery.php
└── Helpers/
    └── MoneyHelper.php
```

### STEP 3: Create Migrations (IN THIS EXACT ORDER)
[Same 24 migrations as before]

### STEP 4: Create Models with Relationships and Collections
Each model should have:
- Proper `$fillable` or `$guarded`
- Relationships (hasMany, belongsTo, morphMany)
- Casts for dates and booleans
- Custom collection class (where needed)
- **NO business logic** (that goes in Actions)

### STEP 5: Create Helper Functions
```php
// app/Helpers/MoneyHelper.php
if (!function_exists('money_to_cents')) {
    function money_to_cents(float $amount): int {
        return (int) round($amount * 100);
    }
}

if (!function_exists('cents_to_money')) {
    function cents_to_money(int $cents): float {
        return $cents / 100;
    }
}

if (!function_exists('format_money')) {
    function format_money(int $cents, string $currency = 'USD'): string {
        return number_format(cents_to_money($cents), 2);
    }
}
```

### STEP 6: Create DTOs (Data Transfer Objects)
```php
// app/DTOs/SaleData.php
class SaleData
{
    public function __construct(
        public int $storeId,
        public ?int $customerId,
        public ?int $registerSessionId,
        public int $subtotal,
        public int $discountAmount,
        public int $totalAmount,
        public string $paymentMethod,
        public int $amountPaid,
        public array $items, // Array of SaleItemData
        public ?array $splitPayments = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            storeId: $data['store_id'],
            customerId: $data['customer_id'] ?? null,
            registerSessionId: $data['register_session_id'] ?? null,
            subtotal: $data['subtotal'],
            discountAmount: $data['discount_amount'] ?? 0,
            totalAmount: $data['total_amount'],
            paymentMethod: $data['payment_method'],
            amountPaid: $data['amount_paid'],
            items: array_map(
                fn($item) => SaleItemData::fromArray($item),
                $data['items']
            ),
            splitPayments: $data['split_payments'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
```

### STEP 7: Create Actions (Single Responsibility)

#### Example: ProcessSaleAction
```php
// app/Actions/Sale/ProcessSaleAction.php
<?php

namespace App\Actions\Sale;

use App\Models\Sale;
use App\DTOs\SaleData;
use App\Actions\Sale\AddSaleItemAction;
use App\Actions\Sale\RecordSalePaymentAction;
use App\Actions\CashRegister\RecordCashTransactionAction;
use Illuminate\Support\Facades\DB;

class ProcessSaleAction
{
    public function __construct(
        private AddSaleItemAction $addSaleItem,
        private RecordSalePaymentAction $recordPayment,
        private RecordCashTransactionAction $recordCashTransaction,
    ) {}

    /**
     * Process a complete sale transaction
     */
    public function execute(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // 1. Create sale record
            $sale = Sale::create([
                'store_id' => $data->storeId,
                'customer_id' => $data->customerId,
                'register_session_id' => $data->registerSessionId,
                'invoice_number' => $this->generateInvoiceNumber(),
                'sale_date' => now(),
                'subtotal' => $data->subtotal,
                'discount_amount' => $data->discountAmount,
                'total_amount' => $data->totalAmount,
                'payment_method' => $data->paymentMethod,
                'amount_paid' => $data->amountPaid,
                'change_given' => max(0, $data->amountPaid - $data->totalAmount),
                'status' => 'completed',
                'notes' => $data->notes,
                'cashier_id' => auth()->id(),
            ]);

            // 2. Add each sale item (FIFO deduction happens here)
            foreach ($data->items as $itemData) {
                $this->addSaleItem->execute($sale, $itemData);
            }

            // 3. Handle split payments
            if ($data->paymentMethod === 'split' && $data->splitPayments) {
                foreach ($data->splitPayments as $payment) {
                    $this->recordPayment->execute($sale, $payment);
                }
            }

            // 4. Record cash transaction if register session active
            if ($data->registerSessionId && in_array($data->paymentMethod, ['cash', 'split'])) {
                $this->recordCashTransaction->execute(
                    registerSessionId: $data->registerSessionId,
                    type: 'sale',
                    amount: $this->calculateCashAmount($sale),
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    description: "Sale {$sale->invoice_number}",
                );
            }

            return $sale->fresh('items.product');
        });
    }

    private function generateInvoiceNumber(): string
    {
        $lastSale = Sale::whereDate('sale_date', today())->latest('id')->first();
        $nextNumber = $lastSale ? ((int) substr($lastSale->invoice_number, -6)) + 1 : 1;
        
        return 'INV-' . date('Ymd') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function calculateCashAmount(Sale $sale): int
    {
        if ($sale->payment_method === 'cash') {
            return $sale->amount_paid;
        }

        return $sale->payments()
            ->where('payment_method', 'cash')
            ->sum('amount');
    }
}
```

#### Example: DeductInventoryFifoAction (⭐ CRITICAL)
```php
// app/Actions/Sale/DeductInventoryFifoAction.php
<?php

namespace App\Actions\Sale;

use App\Models\{SaleItem, Product, InventoryBatch, Inventory, SaleItemBatch};
use Illuminate\Support\Collection;

class DeductInventoryFifoAction
{
    /**
     * Deduct inventory using FIFO and return cost information
     * 
     * @return array{total_cost: int, average_cost: int, batches_used: array}
     */
    public function execute(int $storeId, int $productId, int $quantity, SaleItem $saleItem): array
    {
        // Get oldest batches first (FIFO)
        $batches = InventoryBatch::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('batch_date', 'asc')
            ->get();

        if ($batches->sum('quantity_remaining') < $quantity) {
            $product = Product::find($productId);
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        $totalCost = 0;
        $quantityRemaining = $quantity;
        $batchesUsed = [];

        // Deduct from batches in FIFO order
        foreach ($batches as $batch) {
            if ($quantityRemaining <= 0) break;

            $quantityFromBatch = min($quantityRemaining, $batch->quantity_remaining);
            $costFromBatch = $quantityFromBatch * $batch->unit_cost;

            // Record which batch was used
            SaleItemBatch::create([
                'sale_item_id' => $saleItem->id,
                'inventory_batch_id' => $batch->id,
                'quantity_used' => $quantityFromBatch,
                'unit_cost' => $batch->unit_cost,
            ]);

            // Deduct from batch
            $batch->decrement('quantity_remaining', $quantityFromBatch);

            $batchesUsed[] = [
                'batch_id' => $batch->id,
                'quantity' => $quantityFromBatch,
                'unit_cost' => $batch->unit_cost,
            ];

            $totalCost += $costFromBatch;
            $quantityRemaining -= $quantityFromBatch;
        }

        // Calculate average cost
        $averageCost = (int) round($totalCost / $quantity);

        // Update inventory summary
        Inventory::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->decrement('total_quantity', $quantity);

        return [
            'total_cost' => $totalCost,
            'average_cost' => $averageCost,
            'batches_used' => $batchesUsed,
        ];
    }
}
```

### STEP 8: Create Query Classes
```php
// app/Queries/GetBestSellingProductsQuery.php
<?php

namespace App\Queries;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetBestSellingProductsQuery
{
    public function execute(
        int $storeId,
        string $startDate,
        string $endDate,
        int $limit = 10
    ): Collection {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.store_id', $storeId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.profit) as total_profit'),
            ])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }
}
```

### STEP 9: Create Jobs (Background Tasks)
```php
// app/Jobs/CheckLowStockAlertsJob.php
<?php

namespace App\Jobs;

use App\Queries\GetLowStockProductsQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckLowStockAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $storeId,
    ) {}

    public function handle(GetLowStockProductsQuery $query): void
    {
        $lowStockProducts = $query->execute($this->storeId);

        if ($lowStockProducts->isNotEmpty()) {
            // Send notification to store managers
            // Notification::send($managers, new LowStockAlert($lowStockProducts));
            
            // Log for now
            logger()->warning('Low stock alert', [
                'store_id' => $this->storeId,
                'products' => $lowStockProducts->count(),
            ]);
        }
    }
}
```

### STEP 10: Create Custom Collections
```php
// app/Collections/SaleCollection.php
<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;

class SaleCollection extends Collection
{
    /**
     * Calculate total profit from sales
     */
    public function totalProfit(): int
    {
        return $this->sum(function ($sale) {
            return $sale->items->sum('profit');
        });
    }

    /**
     * Calculate total revenue
     */
    public function totalRevenue(): int
    {
        return $this->sum('total_amount');
    }

    /**
     * Get sales by payment method
     */
    public function byPaymentMethod(): Collection
    {
        return $this->groupBy('payment_method')
            ->map(fn($sales) => [
                'count' => $sales->count(),
                'total' => $sales->sum('total_amount'),
            ]);
    }
}
```

## 📁 COMPLETE FILE STRUCTURE
```
app/
├── Actions/
│   ├── Purchase/
│   │   ├── CreatePurchaseAction.php
│   │   ├── AddPurchaseItemAction.php
│   │   ├── CreateInventoryBatchAction.php
│   │   └── UpdatePurchasePaymentAction.php
│   ├── Sale/
│   │   ├── ProcessSaleAction.php
│   │   ├── AddSaleItemAction.php
│   │   ├── DeductInventoryFifoAction.php
│   │   ├── RecordSalePaymentAction.php
│   │   └── GenerateInvoiceNumberAction.php
│   ├── Inventory/
│   │   ├── AdjustStockAction.php
│   │   ├── UpdateInventorySummaryAction.php
│   │   └── TransferStockAction.php
│   ├── CashRegister/
│   │   ├── OpenRegisterSessionAction.php
│   │   ├── CloseRegisterSessionAction.php
│   │   ├── RecordCashTransactionAction.php
│   │   └── CalculateExpectedCashAction.php
│   ├── Invoice/
│   │   ├── CreateInvoiceAction.php
│   │   ├── AddInvoiceItemAction.php
│   │   ├── RecordInvoicePaymentAction.php
│   │   └── UpdateInvoiceStatusAction.php
│   ├── Return/
│   │   ├── ProcessReturnAction.php
│   │   ├── AddReturnItemAction.php
│   │   └── RestoreInventoryAction.php
│   └── Product/
│       ├── CreateProductAction.php
│       ├── UpdateProductAction.php
│       └── DeleteProductAction.php
├── Collections/
│   ├── ProductCollection.php
│   ├── SaleCollection.php
│   ├── InventoryBatchCollection.php
│   └── PurchaseCollection.php
├── DTOs/
│   ├── PurchaseData.php
│   ├── PurchaseItemData.php
│   ├── SaleData.php
│   ├── SaleItemData.php
│   ├── PaymentData.php
│   ├── InvoiceData.php
│   ├── ReturnData.php
│   └── StockAdjustmentData.php
├── Jobs/
│   ├── CheckLowStockAlertsJob.php
│   ├── GenerateDailyReportJob.php
│   ├── MarkOverdueInvoicesJob.php
│   └── SendReceiptEmailJob.php
├── Queries/
│   ├── GetBestSellingProductsQuery.php
│   ├── CalculateDailyProfitQuery.php
│   ├── GetInventoryValuationQuery.php
│   ├── GetLowStockProductsQuery.php
│   ├── GetRegisterSessionSummaryQuery.php
│   └── GetOverdueInvoicesQuery.php
├── Helpers/
│   └── MoneyHelper.php
└── Models/
    ├── [24 models as specified]
```

## ✅ VALIDATION CHECKLIST

### DATABASE:
- [ ] All 35 tables created
- [ ] All foreign keys with proper constraints
- [ ] All indexes in place
- [ ] All enums with correct values
- [ ] No decimal types (integers only)

### ACTIONS:
- [ ] Each action has ONE public method (execute or handle)
- [ ] Actions use dependency injection
- [ ] Actions call other actions when needed
- [ ] Database transactions in appropriate actions
- [ ] No business logic in models or controllers

### DTOs:
- [ ] All DTOs are readonly classes or use public readonly properties
- [ ] DTOs have fromRequest() static methods
- [ ] DTOs are type-safe

### QUERIES:
- [ ] Query classes return query builder or collection
- [ ] Complex queries extracted from controllers
- [ ] Proper indexes support queries

### JOBS:
- [ ] Jobs implement ShouldQueue
- [ ] Jobs are for async/background tasks only
- [ ] Jobs use dependency injection

### COLLECTIONS:
- [ ] Custom collections extend Eloquent Collection
- [ ] Domain-specific methods added
- [ ] Models specify custom collection class

### NO FORBIDDEN PATTERNS:
- [ ] No Service classes (except third-party)
- [ ] No Repository pattern
- [ ] No Events/Listeners
- [ ] No Observers

## 🚫 COMMON MISTAKES TO AVOID

### ❌ DON'T:
1. **DON'T create Services** - Use Actions instead
```php
   // ❌ WRONG
   class SaleService { }
   
   // ✅ CORRECT
   class ProcessSaleAction { }
```

2. **DON'T put business logic in Models**
```php
   // ❌ WRONG
   class Sale extends Model {
       public function process() { ... }
   }
   
   // ✅ CORRECT
   class ProcessSaleAction {
       public function execute(SaleData $data): Sale { ... }
   }
```

3. **DON'T create Repositories**
```php
   // ❌ WRONG
   class SaleRepository { 
       public function findByDate() { }
   }
   
   // ✅ CORRECT
   class GetSalesByDateQuery {
       public function execute(string $date) { }
   }
```

4. **DON'T use Events/Observers**
```php
   // ❌ WRONG
   class SaleObserver {
       public function created(Sale $sale) { }
   }
   
   // ✅ CORRECT - Call actions explicitly
   $sale = $processSale->execute($data);
   $updateInventory->execute($sale);
```

5. **DON'T put multiple responsibilities in one action**
```php
   // ❌ WRONG
   class ProcessEverythingAction {
       public function execute() {
           // Creates sale
           // Updates inventory
           // Sends email
           // Generates report
       }
   }
   
   // ✅ CORRECT - One action, one responsibility
   class ProcessSaleAction { }
   class UpdateInventoryAction { }
   class SendReceiptEmailJob { } // Background
```

### ✅ DO:
1. **DO use Actions for all business logic**
2. **DO inject dependencies into Actions**
3. **DO use DTOs for data passing**
4. **DO use Query classes for complex queries**
5. **DO use Jobs for async/background tasks**
6. **DO use Collections for data transformation**
7. **DO keep Actions focused (single responsibility)**
8. **DO use database transactions in Actions**

## 📝 DELIVERABLES REQUIRED

Provide these files:

### 1. MIGRATIONS (24 files)
All migration files in correct order

### 2. MODELS (24 files)
With relationships, casts, and custom collections

### 3. ACTIONS (~25 files)
Organized by domain (Purchase, Sale, Invoice, etc.)

### 4. DTOs (~8 files)
For passing data to actions

### 5. QUERIES (~6 files)
For complex database queries

### 6. JOBS (~4 files)
For background tasks

### 7. COLLECTIONS (~4 files)
Custom collection classes

### 8. HELPERS
Money helper functions

### 9. SEEDERS
Role/Permission seeder, optional Store seeder

## 🎯 SUCCESS CRITERIA

Your implementation succeeds when:

1. ✅ All migrations run without errors
2. ✅ All Actions have single responsibility
3. ✅ No Service classes exist (except third-party)
4. ✅ No Repository pattern used
5. ✅ No Events/Observers
6. ✅ DTOs used for data passing
7. ✅ Query classes for complex queries
8. ✅ Jobs for background tasks
9. ✅ FIFO works correctly
10. ✅ All integers (no decimals)

---

## 🔍 ARCHITECTURE EXAMPLES

### Controller Example (using Actions):
```php
class SaleController extends Controller
{
    public function store(
        StoreSaleRequest $request,
        ProcessSaleAction $processSale
    ) {
        $saleData = SaleData::fromRequest($request->validated());
        
        $sale = $processSale->execute($saleData);
        
        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale completed successfully');
    }
}
```

### Action Composition Example:
```php
class ProcessSaleAction
{
    public function __construct(
        private AddSaleItemAction $addSaleItem,
        private RecordSalePaymentAction $recordPayment,
        private RecordCashTransactionAction $recordCashTransaction,
    ) {}

    public function execute(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = $this->createSale($data);
            
            foreach ($data->items as $itemData) {
                $this->addSaleItem->execute($sale, $itemData);
            }
            
            if ($data->splitPayments) {
                foreach ($data->splitPayments as $payment) {
                    $this->recordPayment->execute($sale, $payment);
                }
            }
            
            if ($data->registerSessionId) {
                $this->recordCashTransaction->execute(...);
            }
            
            return $sale;
        });
    }
}
```

---

NOW BEGIN IMPLEMENTATION following Actions Pattern architecture.
Start with migrations, then models, then helpers, then DTOs, then Actions, then Queries, then Jobs, then Collections, then Seeders.

Provide COMPLETE, COPY-PASTE READY code for EVERY file.# TASK: Implement Complete POS & Inventory Management Schema for Laravel 12

## 🎯 PROJECT CONTEXT
You are implementing a Point of Sale (POS) and Inventory Management system for small local stores using:
- **Laravel 12**
- **Actions Pattern** (single responsibility classes)
- **Jobs** for background/queued tasks
- **Query Classes** for complex database queries
- **Collections** for data transformation
- **FIFO (First-In-First-Out)** inventory costing
- **Integer-only** storage (no decimals - all amounts in cents)
- **Multi-store** support (future-ready)
- **Cash register** management with sessions
- **Invoice system** for B2B customers
- **Spatie Laravel Permission** for roles

## 🏗️ ARCHITECTURE PRINCIPLES

### ✅ WHAT WE USE:
1. **ACTIONS** - Single-purpose classes that do ONE thing
    - Example: `CreatePurchaseAction`, `ProcessSaleAction`, `AdjustStockAction`
    - Located in: `app/Actions/`
    - Each action has ONE public method: `execute()` or `handle()`

2. **JOBS** - For queued/background tasks
    - Example: `ProcessLowStockAlertsJob`, `GenerateDailyReportJob`
    - Located in: `app/Jobs/`
    - Use when task can be async or takes time

3. **QUERY CLASSES** - For complex database queries
    - Example: `GetBestSellingProductsQuery`, `CalculateProfitQuery`
    - Located in: `app/Queries/`
    - Return query builder or collection

4. **COLLECTIONS** - For data transformation
    - Custom collection methods for domain logic
    - Example: `ProductCollection`, `SaleCollection`
    - Located in: `app/Collections/`

5. **DATA TRANSFER OBJECTS (DTOs)** - For type-safe data passing
    - Example: `SaleData`, `PurchaseData`
    - Located in: `app/DataTransferObjects/` or `app/DTOs/`

### ❌ WHAT WE DON'T USE:
1. **NO SERVICES** - Services are ONLY for third-party APIs (Stripe, AWS, etc.)
    - Don't create: `SaleService`, `PurchaseService`, etc.
    - Use Actions instead

2. **NO REPOSITORIES** - Use Eloquent directly
    - Models have relationships and scopes
    - Query classes for complex queries

3. **NO EVENTS** - Keep it simple, no event-driven architecture
    - Actions call other actions directly if needed
    - Use Jobs for async tasks

4. **NO OBSERVERS** - Logic should be explicit in actions
    - Don't hide behavior in model observers

## 🚨 CRITICAL CONSTRAINTS - READ CAREFULLY

### ABSOLUTE RULES (NEVER VIOLATE):
1. ❌ **NO DECIMALS** - All monetary values MUST be integers (cents)
2. ❌ **NO TAX CALCULATIONS** - Tax is not used in this system
3. ✅ **FIFO ONLY** - Use FIFO costing via `inventory_batches` table
4. ✅ **INTEGER QUANTITIES** - Except for weight/volume (stored as grams/milliliters)
5. ✅ **PROPER INDEXING** - Add indexes exactly as specified
6. ✅ **CASCADE DELETES** - Use `onDelete('cascade')` where specified
7. ✅ **ACTIONS PATTERN** - Single responsibility, one public method
8. ✅ **NO SERVICES** - Except for third-party integrations

### MONETARY VALUE CONVERSION:
```php
// Always store money as integers (cents)
$10.50 → 1050 (cents)
$100.00 → 10000 (cents)
$0.99 → 99 (cents)

// Helper functions needed:
moneyToCents(10.50) → 1050
centsToMoney(1050) → 10.50
formatMoney(1050) → "$10.50"
```

### QUANTITY STORAGE:
```php
// Pieces: store as-is
5 pieces → 5

// Weight: store in grams
2.5 kg → 2500 (grams)

// Volume: store in milliliters  
1.5 L → 1500 (milliliters)
```

## 📊 COMPLETE SCHEMA TO IMPLEMENT

### DATABASE TABLES (35 Total)

[SAME SCHEMA AS BEFORE - I'll include the full schema here]

#### 1. AUTHENTICATION & AUTHORIZATION
```
users (Laravel default + modifications)
├── id (PK)
├── name (varchar 255)
├── email (varchar 255, unique)
├── password (varchar 255)
├── store_id (FK -> stores, nullable)
├── is_active (boolean, default true)
└── timestamps

stores
├── id (PK)
├── name (varchar 255)
├── address (text, nullable)
├── phone (varchar 20, nullable)
├── currency (varchar 3, default 'USD')
└── timestamps
```

#### 2. PRODUCT CATALOG
```
categories
├── id (PK)
├── name (varchar 100)
├── description (text, nullable)
└── timestamps

products
├── id (PK)
├── category_id (FK -> categories, nullable, nullOnDelete)
├── name (varchar 255)
├── sku (varchar 50, unique, indexed)
├── barcode (varchar 100, unique, indexed, nullable)
├── description (text, nullable)
├── unit (enum: 'piece', 'gram', 'milliliter', default 'piece')
├── selling_price (integer) -- in cents
├── alert_quantity (integer, default 10)
├── image (varchar 255, nullable)
├── is_active (boolean, default true)
└── timestamps

INDEXES: barcode, sku
```

#### 3. INVENTORY (FIFO SYSTEM)
```
inventory
├── id (PK)
├── store_id (FK -> stores, cascadeOnDelete)
├── product_id (FK -> products, cascadeOnDelete)
├── total_quantity (integer, default 0)
└── updated_at

UNIQUE KEY: (store_id, product_id)
INDEX: (store_id, product_id)

inventory_batches (⭐ CRITICAL FOR FIFO)
├── id (PK)
├── store_id (FK -> stores, cascadeOnDelete)
├── product_id (FK -> products, cascadeOnDelete)
├── purchase_item_id (FK -> purchase_items)
├── quantity_received (integer)
├── quantity_remaining (integer)
├── unit_cost (integer) -- in cents
├── batch_date (datetime)
└── timestamps

INDEX: (store_id, product_id, batch_date)
```

#### 4. SUPPLIERS & PURCHASING
```
suppliers
├── id (PK)
├── name (varchar 255)
├── contact_person (varchar 100, nullable)
├── phone (varchar 20, nullable)
├── email (varchar 100, nullable)
├── address (text, nullable)
└── timestamps

purchases
├── id (PK)
├── store_id (FK -> stores)
├── supplier_id (FK -> suppliers, nullable)
├── reference_number (varchar 50, unique, indexed)
├── invoice_number (varchar 100, nullable)
├── purchase_date (date)
├── total_cost (integer) -- in cents
├── paid_amount (integer, default 0) -- in cents
├── payment_status (enum: 'pending', 'partial', 'paid', default 'pending')
├── payment_method (enum: 'cash', 'card', 'bank_transfer', nullable)
├── notes (text, nullable)
├── created_by (FK -> users)
└── timestamps

INDEXES: reference_number, purchase_date, invoice_number

purchase_items
├── id (PK)
├── purchase_id (FK -> purchases, cascadeOnDelete)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_cost (integer) -- in cents
├── subtotal (integer) -- in cents
└── timestamps
```

#### 5. CUSTOMERS
```
customers
├── id (PK)
├── name (varchar 255)
├── phone (varchar 20, unique, nullable)
├── email (varchar 100, unique, nullable)
├── address (text, nullable)
├── customer_type (enum: 'walk-in', 'regular', 'business', default 'walk-in')
└── timestamps

INDEX: phone
```

#### 6. CASH REGISTERS
```
cash_registers
├── id (PK)
├── store_id (FK -> stores)
├── name (varchar 100)
├── description (text, nullable)
├── is_active (boolean, default true)
└── timestamps

register_sessions
├── id (PK)
├── cash_register_id (FK -> cash_registers)
├── opened_by (FK -> users)
├── closed_by (FK -> users, nullable)
├── opening_time (datetime)
├── closing_time (datetime, nullable)
├── opening_balance (integer) -- in cents
├── expected_cash (integer, nullable) -- in cents
├── actual_cash (integer, nullable) -- in cents
├── difference (integer, nullable) -- in cents
├── notes (text, nullable)
├── status (enum: 'open', 'closed', default 'open')
└── timestamps

INDEX: (cash_register_id, status)
```

#### 7. SALES
```
sales
├── id (PK)
├── store_id (FK -> stores)
├── customer_id (FK -> customers, nullable, nullOnDelete)
├── register_session_id (FK -> register_sessions, nullable)
├── invoice_number (varchar 50, unique, indexed)
├── sale_date (datetime)
├── subtotal (integer) -- in cents
├── discount_amount (integer, default 0) -- in cents
├── total_amount (integer) -- in cents
├── payment_method (enum: 'cash', 'card', 'split')
├── amount_paid (integer) -- in cents
├── change_given (integer, default 0) -- in cents
├── status (enum: 'completed', 'pending', 'returned', default 'completed')
├── notes (text, nullable)
├── cashier_id (FK -> users)
└── timestamps

INDEXES: invoice_number, sale_date, (store_id, sale_date)

sale_items
├── id (PK)
├── sale_id (FK -> sales, cascadeOnDelete)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_price (integer) -- in cents
├── unit_cost (integer) -- in cents (from FIFO)
├── subtotal (integer) -- in cents
├── profit (integer) -- in cents
└── timestamps

INDEX: product_id

sale_item_batches (⭐ TRACKS WHICH BATCHES USED)
├── id (PK)
├── sale_item_id (FK -> sale_items, cascadeOnDelete)
├── inventory_batch_id (FK -> inventory_batches)
├── quantity_used (integer)
├── unit_cost (integer) -- in cents
└── created_at

sale_payments
├── id (PK)
├── sale_id (FK -> sales, cascadeOnDelete)
├── payment_method (enum: 'cash', 'card')
├── amount (integer) -- in cents
├── reference_number (varchar 100, nullable)
└── created_at
```

#### 8. INVOICES (B2B)
```
invoices
├── id (PK)
├── store_id (FK -> stores)
├── customer_id (FK -> customers)
├── invoice_number (varchar 50, unique, indexed)
├── invoice_date (date)
├── due_date (date, nullable)
├── subtotal (integer) -- in cents
├── discount_amount (integer, default 0) -- in cents
├── total_amount (integer) -- in cents
├── paid_amount (integer, default 0) -- in cents
├── payment_status (enum: 'unpaid', 'partial', 'paid', 'overdue', default 'unpaid')
├── notes (text, nullable)
├── terms (text, nullable)
├── created_by (FK -> users)
└── timestamps

INDEXES: invoice_number, invoice_date, due_date

invoice_items
├── id (PK)
├── invoice_id (FK -> invoices, cascadeOnDelete)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_price (integer) -- in cents
├── unit_cost (integer) -- in cents (from FIFO)
├── subtotal (integer) -- in cents
├── profit (integer) -- in cents
└── timestamps

invoice_payments
├── id (PK)
├── invoice_id (FK -> invoices)
├── payment_date (date)
├── amount (integer) -- in cents
├── payment_method (enum: 'cash', 'card', 'bank_transfer', 'check')
├── reference_number (varchar 100, nullable)
├── notes (text, nullable)
├── recorded_by (FK -> users)
└── timestamps
```

#### 9. RETURNS
```
returns
├── id (PK)
├── sale_id (FK -> sales, nullable)
├── invoice_id (FK -> invoices, nullable)
├── store_id (FK -> stores)
├── customer_id (FK -> customers, nullable)
├── return_number (varchar 50, unique)
├── return_date (datetime)
├── total_amount (integer) -- in cents
├── refund_method (enum: 'cash', 'card', 'store_credit')
├── reason (text, nullable)
├── processed_by (FK -> users)
└── timestamps

return_items
├── id (PK)
├── return_id (FK -> returns, cascadeOnDelete)
├── sale_item_id (FK -> sale_items, nullable)
├── invoice_item_id (FK -> invoice_items, nullable)
├── product_id (FK -> products)
├── quantity (integer)
├── unit_price (integer) -- in cents
├── unit_cost (integer) -- in cents
├── subtotal (integer) -- in cents
└── timestamps
```

#### 10. STOCK ADJUSTMENTS
```
stock_adjustments
├── id (PK)
├── store_id (FK -> stores)
├── product_id (FK -> products)
├── adjustment_type (enum: 'expired', 'damaged', 'manual', 'correction')
├── quantity (integer) -- negative for removal
├── unit_cost (integer, nullable) -- in cents
├── total_cost (integer, nullable) -- in cents
├── reason (text)
├── adjusted_by (FK -> users)
└── created_at

INDEX: (store_id, created_at)
```

#### 11. CASH TRANSACTIONS
```
cash_transactions
├── id (PK)
├── register_session_id (FK -> register_sessions)
├── transaction_type (enum: 'sale', 'expense', 'withdrawal', 'deposit', 'opening', 'closing')
├── amount (integer) -- in cents (can be negative)
├── reference_type (varchar 255, nullable) -- polymorphic
├── reference_id (bigint unsigned, nullable) -- polymorphic
├── description (text, nullable)
├── created_by (FK -> users)
└── created_at

INDEX: (register_session_id, created_at)

expenses
├── id (PK)
├── store_id (FK -> stores)
├── register_session_id (FK -> register_sessions, nullable)
├── expense_category (enum: 'utilities', 'supplies', 'maintenance', 'other')
├── amount (integer) -- in cents
├── description (text)
├── expense_date (date)
├── recorded_by (FK -> users)
└── timestamps
```

## 🔧 IMPLEMENTATION STEPS

### STEP 1: Setup & Dependencies
```bash
# Install Spatie Permission
composer require spatie/laravel-permission

# Publish migration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### STEP 2: Create Directory Structure
```
app/
├── Actions/
│   ├── Purchase/
│   │   ├── CreatePurchaseAction.php
│   │   ├── AddPurchaseItemAction.php
│   │   └── CreateInventoryBatchAction.php
│   ├── Sale/
│   │   ├── ProcessSaleAction.php
│   │   ├── AddSaleItemAction.php
│   │   ├── DeductInventoryFifoAction.php
│   │   └── RecordSalePaymentAction.php
│   ├── Inventory/
│   │   ├── AdjustStockAction.php
│   │   ├── UpdateInventorySummaryAction.php
│   │   └── CheckLowStockAction.php
│   ├── CashRegister/
│   │   ├── OpenRegisterSessionAction.php
│   │   ├── CloseRegisterSessionAction.php
│   │   └── RecordCashTransactionAction.php
│   ├── Invoice/
│   │   ├── CreateInvoiceAction.php
│   │   ├── RecordInvoicePaymentAction.php
│   │   └── UpdateInvoiceStatusAction.php
│   └── Return/
│       ├── ProcessReturnAction.php
│       └── RestoreInventoryAction.php
├── Collections/
│   ├── ProductCollection.php
│   ├── SaleCollection.php
│   └── InventoryBatchCollection.php
├── DTOs/
│   ├── PurchaseData.php
│   ├── SaleData.php
│   ├── SaleItemData.php
│   └── PaymentData.php
├── Jobs/
│   ├── CheckLowStockAlertsJob.php
│   ├── GenerateDailyReportJob.php
│   └── MarkOverdueInvoicesJob.php
├── Queries/
│   ├── GetBestSellingProductsQuery.php
│   ├── CalculateProfitQuery.php
│   ├── GetInventoryValuationQuery.php
│   └── GetLowStockProductsQuery.php
└── Helpers/
    └── MoneyHelper.php
```

### STEP 3: Create Migrations (IN THIS EXACT ORDER)
[Same 24 migrations as before]

### STEP 4: Create Models with Relationships and Collections
Each model should have:
- Proper `$fillable` or `$guarded`
- Relationships (hasMany, belongsTo, morphMany)
- Casts for dates and booleans
- Custom collection class (where needed)
- **NO business logic** (that goes in Actions)

### STEP 5: Create Helper Functions
```php
// app/Helpers/MoneyHelper.php
if (!function_exists('money_to_cents')) {
    function money_to_cents(float $amount): int {
        return (int) round($amount * 100);
    }
}

if (!function_exists('cents_to_money')) {
    function cents_to_money(int $cents): float {
        return $cents / 100;
    }
}

if (!function_exists('format_money')) {
    function format_money(int $cents, string $currency = 'USD'): string {
        return number_format(cents_to_money($cents), 2);
    }
}
```

### STEP 6: Create DTOs (Data Transfer Objects)
```php
// app/DTOs/SaleData.php
class SaleData
{
    public function __construct(
        public int $storeId,
        public ?int $customerId,
        public ?int $registerSessionId,
        public int $subtotal,
        public int $discountAmount,
        public int $totalAmount,
        public string $paymentMethod,
        public int $amountPaid,
        public array $items, // Array of SaleItemData
        public ?array $splitPayments = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            storeId: $data['store_id'],
            customerId: $data['customer_id'] ?? null,
            registerSessionId: $data['register_session_id'] ?? null,
            subtotal: $data['subtotal'],
            discountAmount: $data['discount_amount'] ?? 0,
            totalAmount: $data['total_amount'],
            paymentMethod: $data['payment_method'],
            amountPaid: $data['amount_paid'],
            items: array_map(
                fn($item) => SaleItemData::fromArray($item),
                $data['items']
            ),
            splitPayments: $data['split_payments'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
```

### STEP 7: Create Actions (Single Responsibility)

#### Example: ProcessSaleAction
```php
// app/Actions/Sale/ProcessSaleAction.php
<?php

namespace App\Actions\Sale;

use App\Models\Sale;
use App\DTOs\SaleData;
use App\Actions\Sale\AddSaleItemAction;
use App\Actions\Sale\RecordSalePaymentAction;
use App\Actions\CashRegister\RecordCashTransactionAction;
use Illuminate\Support\Facades\DB;

class ProcessSaleAction
{
    public function __construct(
        private AddSaleItemAction $addSaleItem,
        private RecordSalePaymentAction $recordPayment,
        private RecordCashTransactionAction $recordCashTransaction,
    ) {}

    /**
     * Process a complete sale transaction
     */
    public function execute(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // 1. Create sale record
            $sale = Sale::create([
                'store_id' => $data->storeId,
                'customer_id' => $data->customerId,
                'register_session_id' => $data->registerSessionId,
                'invoice_number' => $this->generateInvoiceNumber(),
                'sale_date' => now(),
                'subtotal' => $data->subtotal,
                'discount_amount' => $data->discountAmount,
                'total_amount' => $data->totalAmount,
                'payment_method' => $data->paymentMethod,
                'amount_paid' => $data->amountPaid,
                'change_given' => max(0, $data->amountPaid - $data->totalAmount),
                'status' => 'completed',
                'notes' => $data->notes,
                'cashier_id' => auth()->id(),
            ]);

            // 2. Add each sale item (FIFO deduction happens here)
            foreach ($data->items as $itemData) {
                $this->addSaleItem->execute($sale, $itemData);
            }

            // 3. Handle split payments
            if ($data->paymentMethod === 'split' && $data->splitPayments) {
                foreach ($data->splitPayments as $payment) {
                    $this->recordPayment->execute($sale, $payment);
                }
            }

            // 4. Record cash transaction if register session active
            if ($data->registerSessionId && in_array($data->paymentMethod, ['cash', 'split'])) {
                $this->recordCashTransaction->execute(
                    registerSessionId: $data->registerSessionId,
                    type: 'sale',
                    amount: $this->calculateCashAmount($sale),
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    description: "Sale {$sale->invoice_number}",
                );
            }

            return $sale->fresh('items.product');
        });
    }

    private function generateInvoiceNumber(): string
    {
        $lastSale = Sale::whereDate('sale_date', today())->latest('id')->first();
        $nextNumber = $lastSale ? ((int) substr($lastSale->invoice_number, -6)) + 1 : 1;
        
        return 'INV-' . date('Ymd') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function calculateCashAmount(Sale $sale): int
    {
        if ($sale->payment_method === 'cash') {
            return $sale->amount_paid;
        }

        return $sale->payments()
            ->where('payment_method', 'cash')
            ->sum('amount');
    }
}
```

#### Example: DeductInventoryFifoAction (⭐ CRITICAL)
```php
// app/Actions/Sale/DeductInventoryFifoAction.php
<?php

namespace App\Actions\Sale;

use App\Models\{SaleItem, Product, InventoryBatch, Inventory, SaleItemBatch};
use Illuminate\Support\Collection;

class DeductInventoryFifoAction
{
    /**
     * Deduct inventory using FIFO and return cost information
     * 
     * @return array{total_cost: int, average_cost: int, batches_used: array}
     */
    public function execute(int $storeId, int $productId, int $quantity, SaleItem $saleItem): array
    {
        // Get oldest batches first (FIFO)
        $batches = InventoryBatch::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('batch_date', 'asc')
            ->get();

        if ($batches->sum('quantity_remaining') < $quantity) {
            $product = Product::find($productId);
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        $totalCost = 0;
        $quantityRemaining = $quantity;
        $batchesUsed = [];

        // Deduct from batches in FIFO order
        foreach ($batches as $batch) {
            if ($quantityRemaining <= 0) break;

            $quantityFromBatch = min($quantityRemaining, $batch->quantity_remaining);
            $costFromBatch = $quantityFromBatch * $batch->unit_cost;

            // Record which batch was used
            SaleItemBatch::create([
                'sale_item_id' => $saleItem->id,
                'inventory_batch_id' => $batch->id,
                'quantity_used' => $quantityFromBatch,
                'unit_cost' => $batch->unit_cost,
            ]);

            // Deduct from batch
            $batch->decrement('quantity_remaining', $quantityFromBatch);

            $batchesUsed[] = [
                'batch_id' => $batch->id,
                'quantity' => $quantityFromBatch,
                'unit_cost' => $batch->unit_cost,
            ];

            $totalCost += $costFromBatch;
            $quantityRemaining -= $quantityFromBatch;
        }

        // Calculate average cost
        $averageCost = (int) round($totalCost / $quantity);

        // Update inventory summary
        Inventory::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->decrement('total_quantity', $quantity);

        return [
            'total_cost' => $totalCost,
            'average_cost' => $averageCost,
            'batches_used' => $batchesUsed,
        ];
    }
}
```

### STEP 8: Create Query Classes
```php
// app/Queries/GetBestSellingProductsQuery.php
<?php

namespace App\Queries;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetBestSellingProductsQuery
{
    public function execute(
        int $storeId,
        string $startDate,
        string $endDate,
        int $limit = 10
    ): Collection {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.store_id', $storeId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.profit) as total_profit'),
            ])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }
}
```

### STEP 9: Create Jobs (Background Tasks)
```php
// app/Jobs/CheckLowStockAlertsJob.php
<?php

namespace App\Jobs;

use App\Queries\GetLowStockProductsQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckLowStockAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $storeId,
    ) {}

    public function handle(GetLowStockProductsQuery $query): void
    {
        $lowStockProducts = $query->execute($this->storeId);

        if ($lowStockProducts->isNotEmpty()) {
            // Send notification to store managers
            // Notification::send($managers, new LowStockAlert($lowStockProducts));
            
            // Log for now
            logger()->warning('Low stock alert', [
                'store_id' => $this->storeId,
                'products' => $lowStockProducts->count(),
            ]);
        }
    }
}
```

### STEP 10: Create Custom Collections
```php
// app/Collections/SaleCollection.php
<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;

class SaleCollection extends Collection
{
    /**
     * Calculate total profit from sales
     */
    public function totalProfit(): int
    {
        return $this->sum(function ($sale) {
            return $sale->items->sum('profit');
        });
    }

    /**
     * Calculate total revenue
     */
    public function totalRevenue(): int
    {
        return $this->sum('total_amount');
    }

    /**
     * Get sales by payment method
     */
    public function byPaymentMethod(): Collection
    {
        return $this->groupBy('payment_method')
            ->map(fn($sales) => [
                'count' => $sales->count(),
                'total' => $sales->sum('total_amount'),
            ]);
    }
}
```

## 📁 COMPLETE FILE STRUCTURE
```
app/
├── Actions/
│   ├── Purchase/
│   │   ├── CreatePurchaseAction.php
│   │   ├── AddPurchaseItemAction.php
│   │   ├── CreateInventoryBatchAction.php
│   │   └── UpdatePurchasePaymentAction.php
│   ├── Sale/
│   │   ├── ProcessSaleAction.php
│   │   ├── AddSaleItemAction.php
│   │   ├── DeductInventoryFifoAction.php
│   │   ├── RecordSalePaymentAction.php
│   │   └── GenerateInvoiceNumberAction.php
│   ├── Inventory/
│   │   ├── AdjustStockAction.php
│   │   ├── UpdateInventorySummaryAction.php
│   │   └── TransferStockAction.php
│   ├── CashRegister/
│   │   ├── OpenRegisterSessionAction.php
│   │   ├── CloseRegisterSessionAction.php
│   │   ├── RecordCashTransactionAction.php
│   │   └── CalculateExpectedCashAction.php
│   ├── Invoice/
│   │   ├── CreateInvoiceAction.php
│   │   ├── AddInvoiceItemAction.php
│   │   ├── RecordInvoicePaymentAction.php
│   │   └── UpdateInvoiceStatusAction.php
│   ├── Return/
│   │   ├── ProcessReturnAction.php
│   │   ├── AddReturnItemAction.php
│   │   └── RestoreInventoryAction.php
│   └── Product/
│       ├── CreateProductAction.php
│       ├── UpdateProductAction.php
│       └── DeleteProductAction.php
├── Collections/
│   ├── ProductCollection.php
│   ├── SaleCollection.php
│   ├── InventoryBatchCollection.php
│   └── PurchaseCollection.php
├── DTOs/
│   ├── PurchaseData.php
│   ├── PurchaseItemData.php
│   ├── SaleData.php
│   ├── SaleItemData.php
│   ├── PaymentData.php
│   ├── InvoiceData.php
│   ├── ReturnData.php
│   └── StockAdjustmentData.php
├── Jobs/
│   ├── CheckLowStockAlertsJob.php
│   ├── GenerateDailyReportJob.php
│   ├── MarkOverdueInvoicesJob.php
│   └── SendReceiptEmailJob.php
├── Queries/
│   ├── GetBestSellingProductsQuery.php
│   ├── CalculateDailyProfitQuery.php
│   ├── GetInventoryValuationQuery.php
│   ├── GetLowStockProductsQuery.php
│   ├── GetRegisterSessionSummaryQuery.php
│   └── GetOverdueInvoicesQuery.php
├── Helpers/
│   └── MoneyHelper.php
└── Models/
    ├── [24 models as specified]
```

## ✅ VALIDATION CHECKLIST

### DATABASE:
- [ ] All 35 tables created
- [ ] All foreign keys with proper constraints
- [ ] All indexes in place
- [ ] All enums with correct values
- [ ] No decimal types (integers only)

### ACTIONS:
- [ ] Each action has ONE public method (execute or handle)
- [ ] Actions use dependency injection
- [ ] Actions call other actions when needed
- [ ] Database transactions in appropriate actions
- [ ] No business logic in models or controllers

### DTOs:
- [ ] All DTOs are readonly classes or use public readonly properties
- [ ] DTOs have fromRequest() static methods
- [ ] DTOs are type-safe

### QUERIES:
- [ ] Query classes return query builder or collection
- [ ] Complex queries extracted from controllers
- [ ] Proper indexes support queries

### JOBS:
- [ ] Jobs implement ShouldQueue
- [ ] Jobs are for async/background tasks only
- [ ] Jobs use dependency injection

### COLLECTIONS:
- [ ] Custom collections extend Eloquent Collection
- [ ] Domain-specific methods added
- [ ] Models specify custom collection class

### NO FORBIDDEN PATTERNS:
- [ ] No Service classes (except third-party)
- [ ] No Repository pattern
- [ ] No Events/Listeners
- [ ] No Observers

## 🚫 COMMON MISTAKES TO AVOID

### ❌ DON'T:
1. **DON'T create Services** - Use Actions instead
```php
   // ❌ WRONG
   class SaleService { }
   
   // ✅ CORRECT
   class ProcessSaleAction { }
```

2. **DON'T put business logic in Models**
```php
   // ❌ WRONG
   class Sale extends Model {
       public function process() { ... }
   }
   
   // ✅ CORRECT
   class ProcessSaleAction {
       public function execute(SaleData $data): Sale { ... }
   }
```

3. **DON'T create Repositories**
```php
   // ❌ WRONG
   class SaleRepository { 
       public function findByDate() { }
   }
   
   // ✅ CORRECT
   class GetSalesByDateQuery {
       public function execute(string $date) { }
   }
```

4. **DON'T use Events/Observers**
```php
   // ❌ WRONG
   class SaleObserver {
       public function created(Sale $sale) { }
   }
   
   // ✅ CORRECT - Call actions explicitly
   $sale = $processSale->execute($data);
   $updateInventory->execute($sale);
```

5. **DON'T put multiple responsibilities in one action**
```php
   // ❌ WRONG
   class ProcessEverythingAction {
       public function execute() {
           // Creates sale
           // Updates inventory
           // Sends email
           // Generates report
       }
   }
   
   // ✅ CORRECT - One action, one responsibility
   class ProcessSaleAction { }
   class UpdateInventoryAction { }
   class SendReceiptEmailJob { } // Background
```

### ✅ DO:
1. **DO use Actions for all business logic**
2. **DO inject dependencies into Actions**
3. **DO use DTOs for data passing**
4. **DO use Query classes for complex queries**
5. **DO use Jobs for async/background tasks**
6. **DO use Collections for data transformation**
7. **DO keep Actions focused (single responsibility)**
8. **DO use database transactions in Actions**

## 📝 DELIVERABLES REQUIRED

Provide these files:

### 1. MIGRATIONS (24 files)
All migration files in correct order

### 2. MODELS (24 files)
With relationships, casts, and custom collections

### 3. ACTIONS (~25 files)
Organized by domain (Purchase, Sale, Invoice, etc.)

### 4. DTOs (~8 files)
For passing data to actions

### 5. QUERIES (~6 files)
For complex database queries

### 6. JOBS (~4 files)
For background tasks

### 7. COLLECTIONS (~4 files)
Custom collection classes

### 8. HELPERS
Money helper functions

### 9. SEEDERS
Role/Permission seeder, optional Store seeder

## 🎯 SUCCESS CRITERIA

Your implementation succeeds when:

1. ✅ All migrations run without errors
2. ✅ All Actions have single responsibility
3. ✅ No Service classes exist (except third-party)
4. ✅ No Repository pattern used
5. ✅ No Events/Observers
6. ✅ DTOs used for data passing
7. ✅ Query classes for complex queries
8. ✅ Jobs for background tasks
9. ✅ FIFO works correctly
10. ✅ All integers (no decimals)

---

## 🔍 ARCHITECTURE EXAMPLES

### Controller Example (using Actions):
```php
class SaleController extends Controller
{
    public function store(
        StoreSaleRequest $request,
        ProcessSaleAction $processSale
    ) {
        $saleData = SaleData::fromRequest($request->validated());
        
        $sale = $processSale->execute($saleData);
        
        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale completed successfully');
    }
}
```

### Action Composition Example:
```php
class ProcessSaleAction
{
    public function __construct(
        private AddSaleItemAction $addSaleItem,
        private RecordSalePaymentAction $recordPayment,
        private RecordCashTransactionAction $recordCashTransaction,
    ) {}

    public function execute(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = $this->createSale($data);
            
            foreach ($data->items as $itemData) {
                $this->addSaleItem->execute($sale, $itemData);
            }
            
            if ($data->splitPayments) {
                foreach ($data->splitPayments as $payment) {
                    $this->recordPayment->execute($sale, $payment);
                }
            }
            
            if ($data->registerSessionId) {
                $this->recordCashTransaction->execute(...);
            }
            
            return $sale;
        });
    }
}
```

---

I WANT TO IMPLEMENTATION following Actions Pattern architecture.
Start with migrations, then models, then helpers, then DTOs, then Actions, then Queries, then Jobs, then Collections, then Seeders.
