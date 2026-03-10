<?php

declare(strict_types=1);

use App\Http\Controllers\Payments\PaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::prefix('payment-methods')->name('payment-methods.')->group(function (): void {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
        Route::get('/create', [PaymentMethodController::class, 'create'])->name('create');
        Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
        Route::get('/{paymentMethod}/edit', [PaymentMethodController::class, 'edit'])->name('edit');
        Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('update');
        Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
    });

});
