<?php

declare(strict_types=1);

use App\Http\Controllers\Inventory\CancelStockTransferController;
use App\Http\Controllers\Inventory\CompleteStockTransferController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\StockTransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::prefix('stock-transfers')->name('stock-transfers.')->group(function (): void {
        Route::get('/', [StockTransferController::class, 'index'])->name('index');
        Route::get('/create', [StockTransferController::class, 'create'])->name('create');
        Route::post('/', [StockTransferController::class, 'store'])->name('store');
        Route::get('/{stockTransfer}', [StockTransferController::class, 'show'])->name('show');
        Route::get('/{stockTransfer}/edit', [StockTransferController::class, 'edit'])->name('edit');
        Route::put('/{stockTransfer}', [StockTransferController::class, 'update'])->name('update');
        Route::patch('/{stockTransfer}/complete', [CompleteStockTransferController::class])->name('complete');
        Route::patch('/{stockTransfer}/cancel', [CancelStockTransferController::class])->name('cancel');
        Route::delete('/{stockTransfer}', [StockTransferController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('stock-movements')->name('stock-movements.')->group(function (): void {
        Route::get('/', [StockMovementController::class, 'index'])->name('index');
        Route::get('/{stockMovement}', [StockMovementController::class, 'show'])->name('show');
    });

});
