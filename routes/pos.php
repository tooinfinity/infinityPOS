<?php

declare(strict_types=1);

use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\PosProductSearchController;
use App\Http\Controllers\Pos\ReceiptPosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::prefix('pos')->name('pos.')->group(function (): void {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/', [PosController::class, 'store'])->name('store');
        Route::get('/receipt/{sale}', [ReceiptPosController::class])->name('receipt');
        Route::get('/products/search', PosProductSearchController::class)->name('products.search');
    });

});
