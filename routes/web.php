<?php

declare(strict_types=1);

use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Payments\UnvoidPayment;
use App\Http\Controllers\Payments\VoidPayment;
use App\Http\Controllers\Products\BrandMediaController;
use App\Http\Controllers\Products\ProductMediaController;
use App\Http\Controllers\Purchases\PurchaseAttachmentController;
use App\Http\Controllers\Sales\CancelSaleController;
use App\Http\Controllers\Sales\CompleteReturnController;
use App\Http\Controllers\Sales\CompleteSaleController;
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\Sales\ProcessQuickSaleController;
use App\Http\Controllers\Sales\RevertReturnController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Sales\SaleItemController;
use App\Http\Controllers\Sales\SaleReturnController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailResetNotificationController;
use App\Http\Controllers\UserEmailVerificationController;
use App\Http\Controllers\UserEmailVerificationNotificationController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', static fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', static fn () => Inertia::render('dashboard'))->name('dashboard');

    // Brand Logo
    Route::post('brands/{brand}/logo', [BrandMediaController::class, 'store'])
        ->name('brands.logo.store');

    Route::delete('brands/{brand}/logo', [BrandMediaController::class, 'destroy'])
        ->name('brands.logo.destroy');

    // Product Thumbnail
    Route::post('products/{product}/thumbnail', [ProductMediaController::class, 'store'])
        ->name('products.thumbnail.store');

    Route::delete('products/{product}/thumbnail', [ProductMediaController::class, 'destroy'])
        ->name('products.thumbnail.destroy');

    // Purchase Attachment
    Route::post('purchases/{purchase}/attachment', [PurchaseAttachmentController::class, 'store'])
        ->name('purchases.attachment.store');

    Route::delete('purchases/{purchase}/attachment', [PurchaseAttachmentController::class, 'destroy'])
        ->name('purchases.attachment.destroy');

    // POS
    Route::get('pos', [SaleController::class, 'create'])->name('pos.create');
    Route::post('pos/quick-sale', ProcessQuickSaleController::class)->name('pos.quick-sale');

    // Sales
    Route::prefix('sales')->group(function (): void {
        Route::get('/', [SaleController::class, 'index'])->name('sales.index');
        Route::post('/', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/create', [SaleController::class, 'create'])->name('sales.create');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
        Route::patch('/{sale}', [SaleController::class, 'update'])->name('sales.update');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
        Route::post('/{sale}/complete', CompleteSaleController::class)->name('sales.complete');
        Route::post('/{sale}/cancel', CancelSaleController::class)->name('sales.cancel');
        Route::post('/{sale}/items', [SaleItemController::class, 'store'])->name('sales.items.store');
        Route::patch('/{sale}/items/{item}', [SaleItemController::class, 'update'])->name('sales.items.update');
        Route::delete('/{sale}/items/{item}', [SaleItemController::class, 'destroy'])->name('sales.items.destroy');
    });

    // Returns
    Route::prefix('returns')->group(function (): void {
        Route::get('/', [SaleReturnController::class, 'index'])->name('returns.index');
        Route::post('/', [SaleReturnController::class, 'store'])->name('returns.store');
        Route::get('/create', [SaleReturnController::class, 'create'])->name('returns.create');
        Route::get('/{return}', [SaleReturnController::class, 'show'])->name('returns.show');
        Route::get('/{return}/edit', [SaleReturnController::class, 'edit'])->name('returns.edit');
        Route::patch('/{return}', [SaleReturnController::class, 'update'])->name('returns.update');
        Route::delete('/{return}', [SaleReturnController::class, 'destroy'])->name('returns.destroy');
        Route::post('/{return}/complete', CompleteReturnController::class)->name('returns.complete');
        Route::post('/{return}/revert', RevertReturnController::class)->name('returns.revert');
    });

    // Customers
    Route::prefix('customers')->group(function (): void {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::patch('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Payments
    Route::prefix('payments')->group(function (): void {
        Route::post('/', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/{payment}/void', VoidPayment::class)->name('payments.void');
        Route::post('/{payment}/unvoid', UnvoidPayment::class)->name('payments.unvoid');
    });
});

Route::middleware('auth')->group(function (): void {
    // User...
    Route::delete('user', [UserController::class, 'destroy'])->name('user.destroy');

    // User Profile...
    Route::redirect('settings', '/settings/profile');
    Route::get('settings/profile', [UserProfileController::class, 'edit'])->name('user-profile.edit');
    Route::patch('settings/profile', [UserProfileController::class, 'update'])->name('user-profile.update');

    // User Password...
    Route::get('settings/password', [UserPasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [UserPasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    // Appearance...
    Route::get('settings/appearance', fn () => Inertia::render('appearance/update'))->name('appearance.edit');

});

Route::middleware('guest')->group(function (): void {
    // User...
    Route::get('register', [UserController::class, 'create'])
        ->name('register');
    Route::post('register', [UserController::class, 'store'])
        ->name('register.store');

    // User Password...
    Route::get('reset-password/{token}', [UserPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [UserPasswordController::class, 'store'])
        ->name('password.store');

    // User Email Reset Notification...
    Route::get('forgot-password', [UserEmailResetNotificationController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [UserEmailResetNotificationController::class, 'store'])
        ->name('password.email');

    // Session...
    Route::get('login', [SessionController::class, 'create'])
        ->name('login');
    Route::post('login', [SessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    // User Email Verification...
    Route::get('verify-email', [UserEmailVerificationNotificationController::class, 'create'])
        ->name('verification.notice');
    Route::post('email/verification-notification', [UserEmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // User Email Verification...
    Route::get('verify-email/{id}/{hash}', [UserEmailVerificationController::class, 'update'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Session...
    Route::post('logout', [SessionController::class, 'destroy'])
        ->name('logout');
});
