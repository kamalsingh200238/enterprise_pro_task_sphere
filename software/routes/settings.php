<?php

use App\Http\Controllers\OAuthSettingsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'view'])->name('profile.view');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    Route::get('/settings/oauth', [OAuthSettingsController::class, 'show'])
        ->name('oauth-settings.show');

    Route::post('/settings/oauth', [OAuthSettingsController::class, 'update'])
        ->name('oauth-settings.update');
});
