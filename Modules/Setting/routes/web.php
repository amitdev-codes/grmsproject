<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\ApplicationSettingController;
use Modules\Setting\Http\Controllers\ApplicationTranslationController;
use Modules\Setting\Http\Controllers\EmailSettingController;
use Modules\Setting\Http\Controllers\GrievanceIntakeSecuritySettingController;
use Modules\Setting\Http\Controllers\OptimizeAppController;
use Modules\Setting\Http\Controllers\ProfileUpdateController;
use Modules\Setting\Http\Controllers\SmsSettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('edit-profile', [ProfileUpdateController::class, 'edit'])->name('profile.edit');
    Route::post('update-profile', [ProfileUpdateController::class, 'update'])->name('profile.update');

    Route::get('settings/application', [ApplicationSettingController::class, 'edit'])
        ->middleware('role:Super Admin')
        ->name('settings.application.edit');
    Route::patch('settings/application', [ApplicationSettingController::class, 'update'])
        ->middleware('role:Super Admin')
        ->name('settings.application.update');

    Route::middleware('role:Super Admin')->group(function (): void {
        Route::get('settings/security', [GrievanceIntakeSecuritySettingController::class, 'edit'])
            ->name('settings.security.edit');
        Route::put('settings/security', [GrievanceIntakeSecuritySettingController::class, 'update'])
            ->name('settings.security.update');
        Route::get('settings/optimize', [OptimizeAppController::class, 'index'])
            ->name('settings.optimize.index');
        Route::post('settings/optimize', [OptimizeAppController::class, 'run'])
            ->name('settings.optimize.run');
    });

    Route::middleware('role:Super Admin|Admin|IT Admin')->prefix('settings/translations')->name('settings.translations.')->group(function (): void {
        Route::get('/', [ApplicationTranslationController::class, 'index'])->name('index');
        Route::get('/create', [ApplicationTranslationController::class, 'create'])->name('create');
        Route::post('/', [ApplicationTranslationController::class, 'store'])->name('store');
        Route::get('/{translation}/edit', [ApplicationTranslationController::class, 'edit'])->name('edit');
        Route::put('/{translation}', [ApplicationTranslationController::class, 'update'])->name('update');
        Route::delete('/{translation}', [ApplicationTranslationController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:Super Admin')->group(function (): void {
        Route::resource('settings/email', EmailSettingController::class)
            ->parameters(['email' => 'emailSetting'])
            ->names('settings.email')
            ->except(['show']);
        Route::resource('settings/sms', SmsSettingController::class)
            ->parameters(['sms' => 'smsSetting'])
            ->names('settings.sms')
            ->except(['show']);
    });
});
