<?php

declare(strict_types=1);

use App\Http\Controllers\Sales\CompleteSaleReturnController;
use App\Http\Controllers\Sales\SaleReturnController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::prefix('sale-returns')->name('sale-returns.')->group(function (): void {
        Route::get('/', [SaleReturnController::class, 'index'])->name('index');
        Route::get('/create', [SaleReturnController::class, 'create'])->name('create');
        Route::get('/create/{sale}', [SaleReturnController::class, 'create'])->name('create.from-sale');
        Route::post('/', [SaleReturnController::class, 'store'])->name('store');
        Route::get('/{saleReturn}', [SaleReturnController::class, 'show'])->name('show');
        Route::patch('/{saleReturn}/complete', [CompleteSaleReturnController::class])->name('complete');
        Route::delete('/{saleReturn}', [SaleReturnController::class, 'destroy'])->name('destroy');
    });

});
