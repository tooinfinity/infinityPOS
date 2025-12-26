# POS & Inventory System Guidelines

## Project Overview

This is a **Point of Sale (POS) and Inventory Management System** built with Laravel 12 + Inertia.js v2 + React (TypeScript). The system handles:

- **POS Operations**: Real-time cart management, barcode scanning, payment processing
- **Inventory Management**: Stock tracking, transfers, layers, adjustments
- **Sales & Purchases**: Order management, returns, invoicing
- **Multi-store Support**: Store-based inventory and register management
- **Financial Tracking**: Moneyboxes, payments, expenses

---

## Core Architecture Patterns

### 1. Action Pattern (Business Logic Layer)

All business logic lives in **Action classes** under `app/Actions/`:

```
app/Actions/
├── Pos/           # POS-specific actions
├── Sales/         # Sale processing
├── Purchases/     # Purchase processing
├── Inventory/     # Stock management
├── Payments/      # Payment processing
└── ...
```

**Key Principles:**
- Actions have a single `handle()` method
- Inject dependencies via constructor
- Wrap multi-model operations in `DB::transaction()`
- Actions are called from: controllers, jobs, commands, MCP tools
- Create with: `php artisan make:action "{name}" --no-interaction`

**Example:**
```php
final readonly class ProcessPosPayment
{
    public function __construct(
        private CartService $cart,
        private PaymentService $payment
    ) {}

    public function handle(ProcessPosPaymentData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Complex business logic here
        });
    }
}
```

### 2. Data Transfer Objects (DTOs)

All data validation and transformation uses **Spatie Laravel Data** DTOs under `app/Data/`:

```
app/Data/
├── Pos/           # POS cart, payment data
├── Sales/         # Sale creation, updates
├── Inventory/     # Stock adjustments, transfers
└── ...
```

**Benefits:**
- Type-safe data transfer between layers
- Built-in validation
- Automatic casting
- Easy transformation to/from arrays

**Example:**
```php
final class ProcessPosPaymentData extends Data
{
    public function __construct(
        public int $store_id,
        public string $method,  // 'cash', 'card', 'transfer'
        public int $amount,
        public ?int $customer_id = null,
    ) {}
}
```

### 3. Service Layer (Domain Services)

Domain-specific services live under `app/Services/`:

- `CartService`: Draft sale-backed cart management
- `PosConfig`: POS configuration constants
- Other services can be added per domain

**When to use Services vs Actions:**
- **Services**: Stateful, reusable domain logic (e.g., cart management)
- **Actions**: Single-purpose business operations (e.g., process payment)

### 4. Settings Management

Global application settings using **Spatie Laravel Settings**:

```
app/Settings/
├── PosSettings.php         # Barcode scanner, receipt printer, etc.
├── InventorySettings.php   # Stock alerts, auto-deduct, etc.
├── SalesSettings.php       # Discounts, taxes, customer requirements
├── PurchaseSettings.php
├── ReportingSettings.php
└── GeneralSettings.php
```

**Usage:**
```php
use App\Settings\PosSettings;

$settings = app(PosSettings::class);
if ($settings->enable_barcode_scanner) {
    // ...
}
```

### 5. Enums for Domain Values

All status values, types, and constants as **PHP Enums** under `app/Enums/`:

```php
enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case TRANSFER = 'transfer';
}

enum SaleStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
```

**Key Enums:**
- `PaymentMethodEnum`, `PaymentTypeEnum`
- `SaleStatusEnum`, `PurchaseStatusEnum`
- `StockMovementTypeEnum`, `StockTransferStatusEnum`
- `PermissionEnum`, `RoleEnum`

---

## Domain-Specific Patterns

### POS System

#### Cart Management (Draft Sale Pattern)
- Cart is backed by a **draft Sale** record with `status = 'pending'`
- Cart items are stored as `SaleItem` rows
- Draft sale ID stored in `PosRegister.draft_sale_id`
- Each device has unique `pos_device_id` cookie tied to a register

**Flow:**
1. Device cookie → PosRegister lookup
2. Register → draft Sale lookup
3. Cart items = SaleItems where `sale_id = draft_sale_id`
4. On payment: Sale status → 'completed', register cleared

**Service Usage:**
```php
$cartService = app(CartService::class);
$draft = $cartService->getOrCreateDraftSale($userId);
$cartData = $cartService->getRaw(); // Returns items, discount, tax
```

#### Payment Processing
- Must complete sale atomically (sale + payment + stock deduction)
- Use `ProcessPosPayment` action
- Handle change calculation for cash payments
- Support multiple payment methods
- Update moneybox balances

#### Register Management
- Each POS device must be configured with store + moneybox
- Middleware: `EnsurePosDeviceCookie`, `EnsurePosRegisterConfigured`
- Register setup required before first use

### Inventory Management

#### Stock Tracking (FIFO Layers)
- Inventory uses **FIFO layer system** (`inventory_layers` table)
- Each purchase creates new layers
- Sales deduct from oldest layers first
- Track: cost, quantity, remaining quantity per layer

**Key Actions:**
- `CreateInventoryLayer`: Add stock from purchase
- `DeductFromLayers`: Remove stock FIFO on sale
- `AdjustStock`: Manual adjustments
- `RecalculateStockLevels`: Sync product stock totals

#### Stock Transfers
- Multi-step process: `pending` → `completed` / `cancelled`
- Creates stock movements for audit trail
- Updates inventory across stores

#### Stock Movements
- Audit log for all inventory changes
- Types: `sale`, `purchase`, `adjustment`, `transfer`, `return`
- Immutable record with: product, store, type, quantity, reference

### Sales & Purchases

#### Sales Flow
```
1. Draft (POS cart) → 2. Payment → 3. Completed → 4. Invoice (optional)
                                  ↓
                            Stock Deducted
```

**Key Actions:**
- `CreateSale`: Initialize sale
- `ProcessSalePayment`: Complete with payment
- `CalculateSaleTotals`: Compute subtotal, tax, discount
- `CompleteSale`: Finalize without payment (credit)
- `ProcessSaleReturn`: Handle returns

#### Purchases Flow
```
1. Created → 2. Received → 3. Payment → Stock Added
```

**Key Actions:**
- `CreatePurchase`: Initialize purchase order
- `ReceivePurchase`: Mark as received, add inventory layers
- `ProcessPurchasePayment`: Record payment
- `ProcessPurchaseReturn`: Handle returns to supplier

### Payments & Moneyboxes

#### Moneybox System
- **Cash Boxes**: Physical cash storage per store/register
- **Bank Accounts**: Digital payment tracking
- Types: `cash_box`, `bank_account`

**Payment Recording:**
- Sales → Income to moneybox
- Purchases → Expense from moneybox
- Expenses → Deduction from moneybox
- Transfers between moneyboxes supported

#### Payment Actions
- `ProcessPayment`: Generic payment recording
- `RecordMoneyboxTransaction`: Track money movement
- `TransferBetweenMoneyboxes`: Move funds
- `RefundPayment`: Handle refunds

---

## Frontend Architecture (React + Inertia)

### Directory Structure
```
resources/js/
├── pages/              # Inertia page components (lowercase)
│   ├── pos/           # POS interface
│   ├── product/       # Product CRUD
│   ├── category/      # Category CRUD
│   ├── settings/      # Settings pages
│   └── ...
├── components/        # Reusable components
│   ├── ui/           # ShadCN UI primitives
│   ├── pos/          # POS-specific components
│   └── settings/     # Settings components
├── hooks/            # Custom React hooks
├── layouts/          # Page layouts
├── types/            # TypeScript definitions
└── lib/              # Utilities
```

### POS-Specific Components

**Location:** `resources/js/components/pos/`

Key components:
- `cart-sidebar.tsx`: Real-time cart display with item management
- `payment-modal.tsx`: Payment processing interface
- `customer-selector.tsx`: Customer search and selection
- `product-card.tsx`: Product display with quick add
- `register-setup-modal.tsx`: First-time register configuration
- `category-filter.tsx`: Product filtering
- `keyboard-shortcuts-bar.tsx`: Keyboard shortcut help

### Custom Hooks

**Location:** `resources/js/hooks/`

POS-specific hooks:
- `use-pos-cart.ts`: Cart state management
- `use-barcode-scanner.ts`: Barcode scanning integration
- `use-payment-processing.ts`: Payment flow management
- `use-customer-management.ts`: Customer operations
- `use-keyboard-shortcuts.ts`: Keyboard navigation
- `use-pos-modals.ts`: Modal state management

### Type Safety

**Central Types:** `resources/js/types/pos.ts`

Organized by domain:
- Product Types
- Cart Types
- Customer Types
- Register Types
- Payment Types
- Receipt Types
- Settings Types
- API Response Types

**Critical:**
- Types MUST match backend DTOs exactly
- No `any` - use `unknown` and narrow
- Keep types aligned with Laravel Data classes

### POS UX Principles

1. **Speed First**
   - Autofocus first field
   - Enter key submits
   - Strong tab order
   - Keyboard shortcuts (F1-F12)
   - Barcode scanner integration

2. **Error Prevention**
   - Validate stock before adding to cart
   - Confirm destructive actions
   - Show real-time availability

3. **Responsive Design**
   - Must work at 375px width
   - Touch-friendly on tablets
   - Large tap targets (44px min)

4. **Visual Feedback**
   - Skeleton states during loading
   - Toast notifications for actions
   - Clear error messages
   - Success confirmations

5. **Accessibility**
   - Keyboard navigable
   - Screen reader friendly
   - High contrast mode support
   - Dark mode compatible

---

## Testing Strategy

### Test Organization
```
tests/
├── Unit/
│   ├── Actions/        # Test each action independently
│   ├── Data/          # Test DTO validation
│   ├── Models/        # Test model relationships
│   └── Services/      # Test service logic
└── Feature/
    ├── Controllers/    # Test HTTP endpoints
    └── Pos/           # Integration tests for POS flows
```

### POS Testing Requirements

**Unit Tests:**
- Each Action must have tests for happy path, failure path, edge cases
- Test cart calculations independently
- Test FIFO layer deduction logic
- Test payment calculations (change, splits)

**Feature Tests:**
- Test complete POS flows: add to cart → payment → receipt
- Test register setup and configuration
- Test barcode scanning endpoints
- Test customer management
- Test payment processing

**Browser Tests (Pest v4):**
- Full POS workflow with real browser
- Test keyboard shortcuts
- Test barcode scanner simulation
- Test payment modal interactions
- Test responsive behavior

**Example:**
```php
it('completes a POS sale with cash payment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $page = visit('/pos');
    
    $page->assertSee('POS System')
        ->assertNoJavascriptErrors()
        ->type('search-input', '12345') // Barcode
        ->press('Enter')
        ->click('Complete Sale')
        ->fill('amount', '100')
        ->click('Process Payment')
        ->assertSee('Sale Completed')
        ->assertSee('Change: 0.00');
});
```

---

## Permissions & Authorization

### Permission-Based Access

**Enum:** `app/Enums/PermissionEnum.php`

Key permissions:
- POS: `pos_access`, `pos_manage`
- Products: `products_view`, `products_create`, `products_update`, `products_delete`
- Sales: `sales_view`, `sales_create`, `sales_update`, `sales_delete`
- Purchases: `purchases_view`, `purchases_create`, `purchases_update`, `purchases_delete`
- Inventory: `inventory_view`, `inventory_manage`
- Settings: `settings_view`, `settings_update`

### Frontend Permission Checks

**Using the Can component:**
```tsx
import { Can } from '@/components/can';

<Can permission="products_create">
  <Button>Create Product</Button>
</Can>
```

**Using the hook:**
```typescript
import { usePermissions } from '@/hooks/use-permissions';

const { can } = usePermissions();

if (can('sales_create')) {
  // Show create button
}
```

---

## Database Patterns

### Key Tables

**POS Core:**
- `pos_registers`: Device-store-moneybox mapping
- `sales`: Sale records (including draft carts)
- `sale_items`: Line items

**Inventory:**
- `products`: Product catalog
- `inventory_layers`: FIFO stock tracking
- `stock_movements`: Audit trail
- `stock_transfers`: Inter-store transfers

**Financial:**
- `moneyboxes`: Cash boxes and bank accounts
- `moneybox_transactions`: All money movements
- `payments`: Payment records
- `expenses`: Business expenses

**Master Data:**
- `stores`: Physical locations
- `categories`: Product categories
- `brands`: Product brands
- `units`: Measurement units
- `taxes`: Tax rates

### Relationship Patterns

**Polymorphic Relations:**
- Payments can relate to: Sales, Purchases, Expenses
- Stock movements reference various sources

**Soft Deletes:**
- Most models use soft deletes for audit trail
- Check existing model traits

**Timestamps & Auditing:**
- `created_by`, `updated_by` fields track user actions
- Use `auth()->id()` to populate

---

## Configuration & Environment

### Required Environment Variables

```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# App
APP_NAME="Infinity POS"
APP_URL=http://localhost

# Session (important for POS)
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Settings Configuration

Settings are managed via Spatie Laravel Settings. To modify defaults:

```bash
php artisan settings:discover
```

Edit migration files in `database/settings/` for default values.

---

## Common Workflows

### Adding a New Product Feature

1. **Backend:**
   ```bash
   php artisan make:action "Products/ProcessNewFeature"
   php artisan make:data "Products/NewFeatureData"
   # Add method to ProductController
   # Add route to routes/web.php
   ```

2. **Frontend:**
   ```bash
   # Create page component
   # resources/js/pages/product/new-feature.tsx
   
   # Add types to resources/js/types/index.d.ts
   
   # Use Wayfinder for routes
   import { processNewFeature } from '@/actions/...'
   ```

3. **Test:**
   ```bash
   php artisan make:test "Products/ProcessNewFeatureTest" --pest
   php artisan test --filter=ProcessNewFeature
   ```

### Adding a New POS Feature

1. Check `CartService` - does it need extension?
2. Create Action in `app/Actions/Pos/`
3. Add controller method in `app/Http/Controllers/Pos/`
4. Update POS types in `resources/js/types/pos.ts`
5. Add hook if needed in `resources/js/hooks/`
6. Update POS components
7. Test with browser test

### Modifying Inventory Behavior

1. Review FIFO layer logic in `app/Actions/Inventory/`
2. Check if `RecalculateStockLevels` needs update
3. Update `StockMovementTypeEnum` if adding new movement type
4. Ensure audit trail via `CreateStockMovement`
5. Test with complex scenarios (low stock, multiple stores)

---

## Performance Considerations

### Database Optimization

- **Eager Loading**: Always load relationships to prevent N+1
  ```php
  Sale::with(['items.product', 'client', 'store'])->paginate();
  ```

- **Indexing**: Key columns are indexed (see migrations)
  - Foreign keys
  - Status columns
  - Barcode fields
  - Device IDs

### Frontend Optimization

- **Code Splitting**: Wayfinder uses tree-shaking
- **Lazy Loading**: Use Inertia v2 deferred props for heavy data
- **Debouncing**: Use `use-debounce.ts` for search inputs
- **Memoization**: React.memo for expensive components

### POS-Specific Performance

- **Cart Updates**: Debounce quantity changes
- **Product Search**: Server-side search with pagination
- **Barcode Scanning**: Immediate product lookup, cache results
- **Receipt Printing**: Generate async, don't block UI

---

## Security Considerations

### POS Security

1. **Register Isolation**: Each device tied to specific register
2. **Permission Checks**: Enforce `pos_access` permission
3. **Amount Validation**: Server-side validation of all amounts
4. **Audit Trail**: All transactions logged with user ID
5. **Session Security**: Short session lifetime for POS terminals

### Data Protection

1. **SQL Injection**: Use Eloquent/Query Builder exclusively
2. **XSS**: React escapes by default, but validate user input
3. **CSRF**: Inertia handles tokens automatically
4. **Authorization**: Check permissions in controllers AND policies
5. **Sensitive Data**: Don't log payment details

---

## Troubleshooting

### Common POS Issues

**Cart not persisting:**
- Check `pos_device_id` cookie exists
- Verify register is configured
- Check draft sale still exists and is `pending`

**Barcode not scanning:**
- Check `PosSettings::enable_barcode_scanner`
- Verify keyboard input focus
- Test with manual barcode entry

**Stock not deducting:**
- Check `InventorySettings::auto_deduct_stock`
- Verify inventory layers exist
- Check `DeductFromLayers` action logs

**Payment processing fails:**
- Verify moneybox configured for register
- Check payment amount matches cart total
- Review `ProcessPosPayment` action errors

### Development Tips

**Reset POS state:**
```bash
php artisan tinker
>>> CartService::clear(auth()->id())
>>> PosRegister::truncate()
```

**Check inventory layers:**
```bash
php artisan tinker
>>> Product::find(1)->inventoryLayers
```

**View draft sales:**
```bash
php artisan tinker
>>> Sale::where('status', 'pending')->with('items')->get()
```

---

## Migration Guide

### Adding New Domain

1. Create folder structure:
   ```
   app/Actions/NewDomain/
   app/Data/NewDomain/
   app/Http/Controllers/NewDomain/
   ```

2. Create models with factories:
   ```bash
   php artisan make:model NewModel -mfs
   ```

3. Create frontend pages:
   ```
   resources/js/pages/new-domain/
   ```

4. Add routes, permissions, tests

### Extending Existing Features

- Check sibling files for conventions
- Follow existing patterns (Action, DTO, Controller)
- Update enums if adding new statuses/types
- Maintain backward compatibility
- Write tests before modifying

---

## Quick Reference

### Artisan Commands
```bash
# Development
composer run dev          # Start all services
php artisan serve        # Just Laravel
npm run dev             # Just Vite

# Testing
php artisan test                          # All tests
php artisan test --filter=POS            # Specific tests
vendor/bin/pint --dirty                  # Format code

# Code Quality
vendor/bin/phpstan                       # Static analysis
npm run test:types                       # TypeScript check
npm run lint                            # Fix JS/TS lint

# Database
php artisan migrate:fresh --seed         # Reset DB
php artisan db:seed --class=ProductSeeder  # Seed specific
```

### Useful Queries
```php
// Get cart for device
$cartService = app(CartService::class);
$cart = $cartService->getRaw();

// Check user permissions
auth()->user()->can('pos_access');

// Get settings
$posSettings = app(PosSettings::class);
```

---

**Remember:** This is a production POS system. Always test thoroughly, especially for:
- Payment processing (money must be accurate)
- Inventory deduction (stock must be correct)
- Multi-user scenarios (concurrent cart operations)
- Register management (device isolation)
