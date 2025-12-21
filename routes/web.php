<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Http\Controllers\LanguageController;
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

    // Sale Returns...
    Route::get('sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index');
    Route::get('sale-returns/create', [SaleReturnController::class, 'create'])->name('sale-returns.create');
    Route::post('sale-returns', [SaleReturnController::class, 'store'])->name('sale-returns.store');
    Route::get('sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])->name('sale-returns.show');
    Route::post('sale-returns/{saleReturn}/complete', CompleteSaleReturnController::class)->name('sale-returns.complete');
    Route::post('sale-returns/{saleReturn}/cancel', CancelSaleReturnController::class)->name('sale-returns.cancel');

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
