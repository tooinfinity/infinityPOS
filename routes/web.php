<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', 'login');

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');
});

Route::middleware('auth')->group(function (): void {
    // Languages
    Route::post('/locale', [LanguageController::class, 'store'])
        ->name('locale.store');
    // User Management...
    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:'.PermissionEnum::VIEW_USERS->value)
        ->name('users.index');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:'.PermissionEnum::CREATE_USERS->value)
        ->name('users.store');
    Route::patch('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:'.PermissionEnum::EDIT_USERS->value)
        ->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:'.PermissionEnum::DELETE_USERS->value)
        ->name('users.destroy');

    // Settings Index and Group Updates...
    Route::redirect('settings', '/settings/general');
    Route::get('/settings/general', [App\Http\Controllers\Settings\GeneralSettingController::class, 'edit'])->name('settings.general.edit');
    Route::put('/settings/general', [App\Http\Controllers\Settings\GeneralSettingController::class, 'update'])->name('settings.general.update');
    Route::get('/settings/pos', [App\Http\Controllers\Settings\PosSettingController::class, 'edit'])->name('settings.pos.edit');
    Route::put('/settings/pos', [App\Http\Controllers\Settings\PosSettingController::class, 'update'])->name('settings.pos.update');
    Route::get('/settings/inventory', [App\Http\Controllers\Settings\InventorySettingController::class, 'edit'])->name('settings.inventory.edit');
    Route::put('/settings/inventory', [App\Http\Controllers\Settings\InventorySettingController::class, 'update'])->name('settings.inventory.update');
    Route::get('/settings/sales', [App\Http\Controllers\Settings\SalesSettingController::class, 'edit'])->name('settings.sales.edit');
    Route::put('/settings/sales', [App\Http\Controllers\Settings\SalesSettingController::class, 'update'])->name('settings.sales.update');
    Route::get('/settings/purchase', [App\Http\Controllers\Settings\PurchaseSettingController::class, 'edit'])->name('settings.purchase.edit');
    Route::put('/settings/purchase', [App\Http\Controllers\Settings\PurchaseSettingController::class, 'update'])->name('settings.purchase.update');
    Route::get('/settings/reporting', [App\Http\Controllers\Settings\ReportingSettingController::class, 'edit'])->name('settings.reporting.edit');
    Route::put('/settings/reporting', [App\Http\Controllers\Settings\ReportingSettingController::class, 'update'])->name('settings.reporting.update');

    // User Profile...
    // Removed to use actual settings index
    Route::get('settings/profile', [UserProfileController::class, 'edit'])->name('user-profile.edit');
    Route::patch('settings/profile', [UserProfileController::class, 'update'])->name('user-profile.update');
    Route::delete('settings/profile', [UserProfileController::class, 'destroy'])->name('user-profile.destroy');

    // User Password...
    Route::get('settings/password', [UserPasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [UserPasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    // Appearance...
    Route::get('settings/appearance', fn () => Inertia::render('appearance/update'))->name('appearance.edit');

});

Route::middleware('guest')->group(function (): void {

    // User Password...
    Route::get('reset-password/{token}', [UserPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [UserPasswordController::class, 'store'])
        ->name('password.store');

    // Session...
    Route::get('login', [SessionController::class, 'create'])
        ->name('login');
    Route::post('login', [SessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    // Session...
    Route::post('logout', [SessionController::class, 'destroy'])
        ->name('logout');
});
