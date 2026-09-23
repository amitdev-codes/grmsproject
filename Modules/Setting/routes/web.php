<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\ApplicationSettingController;
use Modules\Setting\Http\Controllers\EmailSettingController;
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
