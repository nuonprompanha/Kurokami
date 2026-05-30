<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Admin\Auth\TwoFactorSetupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\ManhwaController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
        Route::get('login/two-factor', [TwoFactorChallengeController::class, 'create'])->name('login.two-factor');
        Route::post('login/two-factor', [TwoFactorChallengeController::class, 'store'])->name('login.two-factor.store');
        Route::get('login/two-factor/setup', [TwoFactorSetupController::class, 'create'])->name('login.two-factor.setup');
        Route::post('login/two-factor/setup', [TwoFactorSetupController::class, 'store'])->name('login.two-factor.setup.store');
        Route::post('login/two-factor/setup/reset', [TwoFactorSetupController::class, 'reset'])->name('login.two-factor.setup.reset');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('profile/two-factor/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('profile.two-factor.confirm');
        Route::post('profile/two-factor/reset', [ProfileController::class, 'resetTwoFactor'])->name('profile.two-factor.reset');

        Route::redirect('two-factor', '/admin/profile?tab=security')->name('two-factor.show');

        Route::middleware('two_factor')->group(function () {
            Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

            Route::resource('manhwas', ManhwaController::class)->except(['show']);
            Route::resource('genres', GenreController::class)->except(['show']);

            Route::middleware('administrator')->group(function () {
                Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
                Route::resource('users', UserController::class)->except(['show']);
            });
        });
    });
});
