<?php

declare(strict_types=1);

use App\Http\Controllers\Purchases\CancelPurchaseController;
use App\Http\Controllers\Purchases\OrderPurchaseController;
use App\Http\Controllers\Purchases\PurchaseAttachmentController;
use App\Http\Controllers\Purchases\PurchaseController;
use App\Http\Controllers\Purchases\ReceivePurchaseController;
use App\Http\Controllers\Purchases\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::prefix('purchases')->name('purchases.')->group(function (): void {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('update');
        Route::patch('/{purchase}/order', OrderPurchaseController::class)->name('order');
        Route::patch('/{purchase}/receive', ReceivePurchaseController::class)->name('receive');
        Route::patch('/{purchase}/cancel', CancelPurchaseController::class)->name('cancel');
        Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('destroy');
        // Purchase Attachment
        Route::post('/{purchase}/attachment', [PurchaseAttachmentController::class, 'store'])
            ->name('attachment.store');

        Route::delete('/{purchase}/attachment', [PurchaseAttachmentController::class, 'destroy'])
            ->name('attachment.destroy');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function (): void {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });

});
