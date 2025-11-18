<?php

declare(strict_types=1);

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/* @phpstan-ignore-next-line */
Route::get('/', fn (): Redirector|\Illuminate\Http\RedirectResponse => redirect('login'));

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');
});

Route::middleware('auth')->group(function (): void {
    // Languages
    Route::post('/locale', [LanguageController::class, 'store'])
        ->name('locale.store');
    // User Management...
    Route::get('users', [UserController::class, 'index'])
        ->name('users.index');
    Route::post('users', [UserController::class, 'store'])
        ->name('users.store');
    Route::patch('users/{user}', [UserController::class, 'update'])
        ->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy');

    // User Profile...
    Route::redirect('settings', '/settings/profile');
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
