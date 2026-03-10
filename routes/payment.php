<?php

declare(strict_types=1);

use App\Http\Controllers\Payments\PurchasePaymentController;
use App\Http\Controllers\Payments\PurchaseReturnPaymentController;
use App\Http\Controllers\Payments\SalePaymentController;
use App\Http\Controllers\Payments\SaleReturnPaymentController;
use App\Http\Controllers\Payments\VoidPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::post(
        'sales/{sale}/payments',
        [SalePaymentController::class]
    )->name('sales.payments.store');

    Route::post(
        'purchases/{purchase}/payments',
        [PurchasePaymentController::class]
    )->name('purchases.payments.store');

    Route::post(
        'sale-returns/{saleReturn}/payments',
        [SaleReturnPaymentController::class]
    )->name('sale-returns.payments.store');

    Route::post(
        'purchase-returns/{purchaseReturn}/payments',
        [PurchaseReturnPaymentController::class]
    )->name('purchase-returns.payments.store');

    Route::patch(
        'payments/{payment}/void',
        [VoidPaymentController::class]
    )->name('payments.void');

});
