<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', fn () => Inertia::render('settings/Appearance'))->name('appearance');

    Route::get('settings/session', [SessionController::class, 'index'])->name('session.index');
    Route::delete('settings/session', [SessionController::class, 'destroy'])->name('session.destroy');

    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});
