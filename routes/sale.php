<?php

declare(strict_types=1);

use App\Http\Controllers\Sales\CancelSaleController;
use App\Http\Controllers\Sales\CompleteSaleController;
use App\Http\Controllers\Sales\SaleController;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
        Route::put('/{sale}', [SaleController::class, 'update'])->name('update');
        Route::patch('/{sale}/complete', [CompleteSaleController::class])->name('complete');
        Route::patch('/{sale}/cancel', [CancelSaleController::class])->name('cancel');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');
    });

});
