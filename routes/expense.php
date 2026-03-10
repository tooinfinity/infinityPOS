<?php

declare(strict_types=1);

use App\Http\Controllers\Expenses\ExpenseCategoryController;
use App\Http\Controllers\Expenses\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
        Route::get('/', [ExpenseCategoryController::class, 'index'])->name('index');
        Route::get('/create', [ExpenseCategoryController::class, 'create'])->name('create');
        Route::post('/', [ExpenseCategoryController::class, 'store'])->name('store');
        Route::get('/{expenseCategory}/edit', [ExpenseCategoryController::class, 'edit'])->name('edit');
        Route::put('/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('update');
        Route::delete('/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/create', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

});
