<?php

declare(strict_types=1);

use App\Http\Controllers\Products\BrandMediaController;
use App\Http\Controllers\Products\ProductMediaController;
use App\Http\Controllers\Purchases\PurchaseAttachmentController;
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

require __DIR__.'/sale.php';
require __DIR__.'/purchase.php';
require __DIR__.'/inventory.php';
require __DIR__.'/sale-return.php';
