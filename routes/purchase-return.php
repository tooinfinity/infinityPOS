<?php

declare(strict_types=1);
use App\Http\Controllers\Purchases\CompletePurchaseReturnController;
use App\Http\Controllers\Purchases\PurchaseReturnController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::prefix('purchase-returns')->name('purchase-returns.')->group(function (): void {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
        Route::get('/create/{purchase}', [PurchaseReturnController::class, 'create'])->name('create.from-purchase');
        Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
        Route::get('/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('show');
        Route::patch('/{purchaseReturn}/complete', CompletePurchaseReturnController::class)->name('complete');
        Route::delete('/{purchaseReturn}', [PurchaseReturnController::class, 'destroy'])->name('destroy');
    });

});
