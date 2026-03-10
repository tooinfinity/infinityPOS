<?php

declare(strict_types=1);

use App\Http\Controllers\Products\BatchController;
use App\Http\Controllers\Products\BrandController;
use App\Http\Controllers\Products\BrandMediaController;
use App\Http\Controllers\Products\CategoryController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Products\ProductMediaController;
use App\Http\Controllers\Products\UnitController;
use App\Http\Controllers\Products\WarehouseController;

Illuminate\Support\Facades\Route::middleware(['auth', 'verified'])->group(function (): void {

    // ── Products ─────────────────────────────────────────────────────────────
    Illuminate\Support\Facades\Route::prefix('products')->name('products.')->group(function (): void {
        Illuminate\Support\Facades\Route::get('/', [ProductController::class, 'index'])->name('index');
        Illuminate\Support\Facades\Route::get('/create', [ProductController::class, 'create'])->name('create');
        Illuminate\Support\Facades\Route::post('/', [ProductController::class, 'store'])->name('store');
        Illuminate\Support\Facades\Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Illuminate\Support\Facades\Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Illuminate\Support\Facades\Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Illuminate\Support\Facades\Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

        // Product media
        Illuminate\Support\Facades\Route::post('/{product}/thumbnail', [ProductMediaController::class, 'store'])
            ->name('thumbnail.store');
        Illuminate\Support\Facades\Route::delete('/{product}/thumbnail', [ProductMediaController::class, 'destroy'])
            ->name('thumbnail.destroy');
    });

    // ── Brands ───────────────────────────────────────────────────────────────
    Illuminate\Support\Facades\Route::prefix('brands')->name('brands.')->group(function (): void {
        Illuminate\Support\Facades\Route::get('/', [BrandController::class, 'index'])->name('index');
        Illuminate\Support\Facades\Route::get('/create', [BrandController::class, 'create'])->name('create');
        Illuminate\Support\Facades\Route::post('/', [BrandController::class, 'store'])->name('store');
        Illuminate\Support\Facades\Route::get('/{brand}/edit', [BrandController::class, 'edit'])->name('edit');
        Illuminate\Support\Facades\Route::put('/{brand}', [BrandController::class, 'update'])->name('update');
        Illuminate\Support\Facades\Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('destroy');

        // Brand logo
        Illuminate\Support\Facades\Route::post('/{brand}/logo', [BrandMediaController::class, 'store'])
            ->name('logo.store');
        Illuminate\Support\Facades\Route::delete('/{brand}/logo', [BrandMediaController::class, 'destroy'])
            ->name('logo.destroy');
    });

    // ── Categories ───────────────────────────────────────────────────────────
    Illuminate\Support\Facades\Route::prefix('categories')->name('categories.')->group(function (): void {
        Illuminate\Support\Facades\Route::get('/', [CategoryController::class, 'index'])->name('index');
        Illuminate\Support\Facades\Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Illuminate\Support\Facades\Route::post('/', [CategoryController::class, 'store'])->name('store');
        Illuminate\Support\Facades\Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Illuminate\Support\Facades\Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Illuminate\Support\Facades\Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // ── Units ─────────────────────────────────────────────────────────────────
    Illuminate\Support\Facades\Route::prefix('units')->name('units.')->group(function (): void {
        Illuminate\Support\Facades\Route::get('/', [UnitController::class, 'index'])->name('index');
        Illuminate\Support\Facades\Route::get('/create', [UnitController::class, 'create'])->name('create');
        Illuminate\Support\Facades\Route::post('/', [UnitController::class, 'store'])->name('store');
        Illuminate\Support\Facades\Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit');
        Illuminate\Support\Facades\Route::put('/{unit}', [UnitController::class, 'update'])->name('update');
        Illuminate\Support\Facades\Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
    });

    // ── Warehouses ────────────────────────────────────────────────────────────
    Illuminate\Support\Facades\Route::prefix('warehouses')->name('warehouses.')->group(function (): void {
        Illuminate\Support\Facades\Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Illuminate\Support\Facades\Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Illuminate\Support\Facades\Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Illuminate\Support\Facades\Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
        Illuminate\Support\Facades\Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Illuminate\Support\Facades\Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Illuminate\Support\Facades\Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
    });

    // ── Batches ───────────────────────────────────────────────────────────────
    Illuminate\Support\Facades\Route::prefix('batches')->name('batches.')->group(function (): void {
        Illuminate\Support\Facades\Route::get('/', [BatchController::class, 'index'])->name('index');
        Illuminate\Support\Facades\Route::get('/create', [BatchController::class, 'create'])->name('create');
        Illuminate\Support\Facades\Route::post('/', [BatchController::class, 'store'])->name('store');
        Illuminate\Support\Facades\Route::get('/{batch}', [BatchController::class, 'show'])->name('show');
        Illuminate\Support\Facades\Route::get('/{batch}/edit', [BatchController::class, 'edit'])->name('edit');
        Illuminate\Support\Facades\Route::put('/{batch}', [BatchController::class, 'update'])->name('update');
        Illuminate\Support\Facades\Route::delete('/{batch}', [BatchController::class, 'destroy'])->name('destroy');
    });

});
