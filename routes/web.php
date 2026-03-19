<?php

declare(strict_types=1);

use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailResetNotificationController;
use App\Http\Controllers\UserPasswordController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', static fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', static fn () => Inertia::render('dashboard'))->name('dashboard');
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

require __DIR__.'/auth.php';
require __DIR__.'/product.php';
require __DIR__.'/sale.php';
require __DIR__.'/purchase.php';
require __DIR__.'/inventory.php';
require __DIR__.'/sale-return.php';
require __DIR__.'/purchase-return.php';
require __DIR__.'/payment.php';
require __DIR__.'/payment-method.php';
require __DIR__.'/expense.php';
require __DIR__.'/pos.php';
