<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Http\Controllers\Brands\BrandController;
use App\Http\Controllers\Categories\CategoryController;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Expenses\ExpenseController;
use App\Http\Controllers\Inventory\BulkStockAdjustmentController;
use App\Http\Controllers\Inventory\CancelStockTransferController;
use App\Http\Controllers\Inventory\CompleteStockTransferController;
use App\Http\Controllers\Inventory\InventoryLevelController;
use App\Http\Controllers\Inventory\RecalculateStockLevelController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\StockTransferController;
use App\Http\Controllers\Invoices\CancelInvoiceController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\Invoices\MarkInvoiceAsPaidController;
use App\Http\Controllers\Invoices\SendInvoiceEmailController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Moneyboxes\MoneyboxController;
use App\Http\Controllers\Moneyboxes\MoneyboxTransactionController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Payments\RefundPaymentController;
use App\Http\Controllers\Payments\VoidPaymentController;
use App\Http\Controllers\Pos\AddCartItemController;
use App\Http\Controllers\Pos\ApplyCartDiscountController;
use App\Http\Controllers\Pos\ApplyCartTaxController;
use App\Http\Controllers\Pos\CartController;
use App\Http\Controllers\Pos\ClearCartController;
use App\Http\Controllers\Pos\ClearRegisterCartController;
use App\Http\Controllers\Pos\PaymentController as PosPaymentController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\ProductSearchController;
use App\Http\Controllers\Pos\ReceiptController;
use App\Http\Controllers\Pos\RemoveCartItemController;
use App\Http\Controllers\Pos\UpdateCartItemController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Purchases\CancelPurchaseController;
use App\Http\Controllers\Purchases\CancelPurchaseReturnController;
use App\Http\Controllers\Purchases\CompletePurchaseReturnController;
use App\Http\Controllers\Purchases\PurchaseController;
use App\Http\Controllers\Purchases\PurchaseItemController;
use App\Http\Controllers\Purchases\PurchasePaymentController;
use App\Http\Controllers\Purchases\PurchaseReturnController;
use App\Http\Controllers\Purchases\ReceivePurchaseController;
use App\Http\Controllers\Sales\CancelSaleController;
use App\Http\Controllers\Sales\CancelSaleReturnController;
use App\Http\Controllers\Sales\CompleteSaleController;
use App\Http\Controllers\Sales\CompleteSaleReturnController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Sales\SaleInvoiceController;
use App\Http\Controllers\Sales\SaleItemController;
use App\Http\Controllers\Sales\SalePaymentController;
use App\Http\Controllers\Sales\SaleReturnController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\Stores\StoreController;
use App\Http\Controllers\Suppliers\SupplierController;
use App\Http\Controllers\Taxes\TaxController;
use App\Http\Controllers\Units\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', 'login');

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');
});

Route::middleware('auth')->group(function (): void {
    // Languages
    Route::post('/locale', [LanguageController::class, 'store'])
        ->name('locale.store');
    // User Management...
    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:'.PermissionEnum::VIEW_USERS->value)
        ->name('users.index');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:'.PermissionEnum::CREATE_USERS->value)
        ->name('users.store');
    Route::patch('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:'.PermissionEnum::EDIT_USERS->value)
        ->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:'.PermissionEnum::DELETE_USERS->value)
        ->name('users.destroy');

    // Settings Index and Group Updates...
    Route::redirect('settings', '/settings/general');
    Route::get('/settings/general', [App\Http\Controllers\Settings\GeneralSettingController::class, 'edit'])->name('settings.general.edit');
    Route::put('/settings/general', [App\Http\Controllers\Settings\GeneralSettingController::class, 'update'])->name('settings.general.update');
    Route::get('/settings/pos', [App\Http\Controllers\Settings\PosSettingController::class, 'edit'])->name('settings.pos.edit');
    Route::put('/settings/pos', [App\Http\Controllers\Settings\PosSettingController::class, 'update'])->name('settings.pos.update');
    Route::get('/settings/inventory', [App\Http\Controllers\Settings\InventorySettingController::class, 'edit'])->name('settings.inventory.edit');
    Route::put('/settings/inventory', [App\Http\Controllers\Settings\InventorySettingController::class, 'update'])->name('settings.inventory.update');
    Route::get('/settings/sales', [App\Http\Controllers\Settings\SalesSettingController::class, 'edit'])->name('settings.sales.edit');
    Route::put('/settings/sales', [App\Http\Controllers\Settings\SalesSettingController::class, 'update'])->name('settings.sales.update');
    Route::get('/settings/purchase', [App\Http\Controllers\Settings\PurchaseSettingController::class, 'edit'])->name('settings.purchase.edit');
    Route::put('/settings/purchase', [App\Http\Controllers\Settings\PurchaseSettingController::class, 'update'])->name('settings.purchase.update');
    Route::get('/settings/reporting', [App\Http\Controllers\Settings\ReportingSettingController::class, 'edit'])->name('settings.reporting.edit');
    Route::put('/settings/reporting', [App\Http\Controllers\Settings\ReportingSettingController::class, 'update'])->name('settings.reporting.update');

    // User Profile...
    // Removed to use actual settings index
    Route::get('settings/profile', [UserProfileController::class, 'edit'])->name('user-profile.edit');
    Route::patch('settings/profile', [UserProfileController::class, 'update'])->name('user-profile.update');
    Route::delete('settings/profile', [UserProfileController::class, 'destroy'])->name('user-profile.destroy');

    // User Password...
    Route::get('settings/password', [UserPasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [UserPasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    // Appearance...
    Route::get('settings/appearance', fn () => Inertia::render('appearance/update'))->name('appearance.edit');

    // API Routes for POS (JSON responses)
    Route::prefix('api')->group(function (): void {
        Route::get('clients/search', App\Http\Controllers\Api\ClientSearchController::class)->name('api.clients.search');
        Route::post('clients', [ClientController::class, 'store'])->name('api.clients.store');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('api.clients.show');
        Route::patch('clients/{client}', [ClientController::class, 'update'])->name('api.clients.update');
    });

    // POS...
    Route::prefix('pos')
        ->as('pos.')
        ->middleware([App\Http\Middleware\EnsurePosDeviceCookie::class, 'permission:'.PermissionEnum::ACCESS_POS->value, App\Http\Middleware\EnsurePosRegisterConfigured::class])
        ->group(function (): void {
            // UI
            Route::get('/', [PosController::class, 'index'])->name('index');

            // Register setup (per-device)
            Route::get('/register', [App\Http\Controllers\Pos\RegisterController::class, 'edit'])->name('register.edit');
            Route::put('/register', [App\Http\Controllers\Pos\RegisterController::class, 'update'])->name('register.update');
            Route::delete('/register/cart', ClearRegisterCartController::class)->name('register.cart.clear');

            // Product search (query/barcode)
            Route::get('/products', [ProductSearchController::class, 'index'])->name('products.index');

            // Cart endpoints
            Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
            Route::post('/cart/items', AddCartItemController::class)->name('cart.items.store');
            Route::patch('/cart/items/{lineId}', UpdateCartItemController::class)->name('cart.items.update');
            Route::delete('/cart/items/{lineId}', RemoveCartItemController::class)->name('cart.items.destroy');
            Route::put('/cart/discount', ApplyCartDiscountController::class)->name('cart.discount.update');
            Route::put('/cart/tax', ApplyCartTaxController::class)->name('cart.tax.update');
            Route::delete('/cart', ClearCartController::class)->name('cart.clear');

            // Payments
            Route::post('/payments', new PosPaymentController()->store(...))->name('payments.store');

            // Receipts (placeholder)
            Route::get('/receipts/{sale}', [ReceiptController::class, 'show'])->name('receipts.show');
        });

    // Sales Management...
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
    Route::patch('sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
    Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::post('sales/{sale}/complete', CompleteSaleController::class)->name('sales.complete');
    Route::post('sales/{sale}/cancel', CancelSaleController::class)->name('sales.cancel');

    // Sale Items...
    Route::post('sales/{sale}/items', [SaleItemController::class, 'store'])->name('sales.items.store');
    Route::patch('sales/{sale}/items/{item}', [SaleItemController::class, 'update'])->name('sales.items.update');
    Route::delete('sales/{sale}/items/{item}', [SaleItemController::class, 'destroy'])->name('sales.items.destroy');

    // Sale Payments...
    Route::post('sales/{sale}/payments', [SalePaymentController::class, 'store'])->name('sales.payments.store');

    // Sale Invoices...
    Route::post('sales/{sale}/invoices', [SaleInvoiceController::class, 'store'])->name('sales.invoices.store');

    // Sale Returns..
    Route::get('sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index');
    Route::get('sale-returns/create', [SaleReturnController::class, 'create'])->name('sale-returns.create');
    Route::post('sale-returns', [SaleReturnController::class, 'store'])->name('sale-returns.store');
    Route::get('sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])->name('sale-returns.show');
    Route::post('sale-returns/{saleReturn}/complete', CompleteSaleReturnController::class)->name('sale-returns.complete');
    Route::post('sale-returns/{saleReturn}/cancel', CancelSaleReturnController::class)->name('sale-returns.cancel');

    // Purchase Management..
    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::patch('purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    Route::post('purchases/{purchase}/receive', ReceivePurchaseController::class)->name('purchases.receive');
    Route::post('purchases/{purchase}/cancel', CancelPurchaseController::class)->name('purchases.cancel');

    // Purchase Items..
    Route::post('purchases/{purchase}/items', [PurchaseItemController::class, 'store'])->name('purchases.items.store');
    Route::patch('purchases/{purchase}/items/{item}', [PurchaseItemController::class, 'update'])->name('purchases.items.update');
    Route::delete('purchases/{purchase}/items/{item}', [PurchaseItemController::class, 'destroy'])->name('purchases.items.destroy');

    // Purchase Payments..
    Route::post('purchases/{purchase}/payments', [PurchasePaymentController::class, 'store'])->name('purchases.payments.store');

    // Purchase Returns..
    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
    Route::get('purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
    Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
    Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
    Route::post('purchase-returns/{purchaseReturn}/complete', CompletePurchaseReturnController::class)->name('purchase-returns.complete');
    Route::post('purchase-returns/{purchaseReturn}/cancel', CancelPurchaseReturnController::class)->name('purchase-returns.cancel');

    // Inventory - Stock Transfers..
    Route::get('inventory/stock-transfers', [StockTransferController::class, 'index'])->name('inventory.stock-transfers.index');
    Route::get('inventory/stock-transfers/create', [StockTransferController::class, 'create'])->name('inventory.stock-transfers.create');
    Route::post('inventory/stock-transfers', [StockTransferController::class, 'store'])->name('inventory.stock-transfers.store');
    Route::get('inventory/stock-transfers/{stockTransfer}', [StockTransferController::class, 'show'])->name('inventory.stock-transfers.show');
    Route::post('inventory/stock-transfers/{stockTransfer}/complete', CompleteStockTransferController::class)->name('inventory.stock-transfers.complete');
    Route::post('inventory/stock-transfers/{stockTransfer}/cancel', CancelStockTransferController::class)->name('inventory.stock-transfers.cancel');

    // Inventory - Stock Adjustments..
    Route::get('inventory/adjustments', [StockAdjustmentController::class, 'index'])->name('inventory.adjustments.index');
    Route::get('inventory/adjustments/create', [StockAdjustmentController::class, 'create'])->name('inventory.adjustments.create');
    Route::post('inventory/adjustments', [StockAdjustmentController::class, 'store'])->name('inventory.adjustments.store');

    // Inventory - Bulk Adjustments..
    Route::get('inventory/bulk-adjustments/create', [BulkStockAdjustmentController::class, 'create'])->name('inventory.bulk-adjustments.create');
    Route::post('inventory/bulk-adjustments', [BulkStockAdjustmentController::class, 'store'])->name('inventory.bulk-adjustments.store');

    // Inventory - Stock Levels..
    Route::get('inventory/levels', [InventoryLevelController::class, 'index'])->name('inventory.levels.index');
    Route::get('inventory/levels/{product}/{store}', [InventoryLevelController::class, 'show'])->name('inventory.levels.show');
    Route::post('inventory/levels/{product}/{store}/recalculate', RecalculateStockLevelController::class)->name('inventory.levels.recalculate');

    // Inventory - Stock Movements..
    Route::get('inventory/movements', [StockMovementController::class, 'index'])->name('inventory.movements.index');
    Route::get('inventory/movements/{product}', [StockMovementController::class, 'show'])->name('inventory.movements.show');

    // Payments..
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/refund', RefundPaymentController::class)->name('payments.refund');
    Route::post('payments/{payment}/void', VoidPaymentController::class)->name('payments.void');

    // Invoices..
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::patch('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::post('invoices/{invoice}/cancel', CancelInvoiceController::class)->name('invoices.cancel');
    Route::post('invoices/{invoice}/mark-as-paid', MarkInvoiceAsPaidController::class)->name('invoices.mark-as-paid');
    Route::post('invoices/{invoice}/send-email', SendInvoiceEmailController::class)->name('invoices.send-email');

    // Catalog..
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('brands/create', [BrandController::class, 'create'])->name('brands.create');
    Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
    Route::get('brands/{brand}', [BrandController::class, 'show'])->name('brands.show');
    Route::get('brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::patch('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('units', [UnitController::class, 'index'])->name('units.index');
    Route::get('units/create', [UnitController::class, 'create'])->name('units.create');
    Route::post('units', [UnitController::class, 'store'])->name('units.store');
    Route::get('units/{unit}', [UnitController::class, 'show'])->name('units.show');
    Route::get('units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
    Route::patch('units/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    Route::get('taxes', [TaxController::class, 'index'])->name('taxes.index');
    Route::get('taxes/create', [TaxController::class, 'create'])->name('taxes.create');
    Route::post('taxes', [TaxController::class, 'store'])->name('taxes.store');
    Route::get('taxes/{tax}', [TaxController::class, 'show'])->name('taxes.show');
    Route::get('taxes/{tax}/edit', [TaxController::class, 'edit'])->name('taxes.edit');
    Route::patch('taxes/{tax}', [TaxController::class, 'update'])->name('taxes.update');
    Route::delete('taxes/{tax}', [TaxController::class, 'destroy'])->name('taxes.destroy');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Clients & Suppliers..
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::patch('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::patch('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Stores..
    Route::get('stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('stores/{store}', [StoreController::class, 'show'])->name('stores.show');
    Route::get('stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');
    Route::patch('stores/{store}', [StoreController::class, 'update'])->name('stores.update');
    Route::delete('stores/{store}', [StoreController::class, 'destroy'])->name('stores.destroy');

    // Expenses..
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Moneyboxes..
    Route::get('moneyboxes', [MoneyboxController::class, 'index'])->name('moneyboxes.index');
    Route::get('moneyboxes/create', [MoneyboxController::class, 'create'])->name('moneyboxes.create');
    Route::post('moneyboxes', [MoneyboxController::class, 'store'])->name('moneyboxes.store');

    // Moneybox Transactions (before {moneybox} routes to avoid conflicts)..
    Route::get('moneybox-transactions', [MoneyboxTransactionController::class, 'index'])->name('moneyboxes.transactions.index');
    Route::get('moneyboxes/{moneybox}/transactions', [MoneyboxTransactionController::class, 'show'])->name('moneyboxes.transactions.show');

    Route::get('moneyboxes/{moneybox}', [MoneyboxController::class, 'show'])->name('moneyboxes.show');
    Route::get('moneyboxes/{moneybox}/edit', [MoneyboxController::class, 'edit'])->name('moneyboxes.edit');
    Route::patch('moneyboxes/{moneybox}', [MoneyboxController::class, 'update'])->name('moneyboxes.update');
    Route::delete('moneyboxes/{moneybox}', [MoneyboxController::class, 'destroy'])->name('moneyboxes.destroy');

});

Route::middleware('guest')->group(function (): void {

    // User Password...
    Route::get('reset-password/{token}', [UserPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [UserPasswordController::class, 'store'])
        ->name('password.store');

    // Session...
    Route::get('login', [SessionController::class, 'create'])
        ->name('login');
    Route::post('login', [SessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    // Session...
    Route::post('logout', [SessionController::class, 'destroy'])
        ->name('logout');
});
