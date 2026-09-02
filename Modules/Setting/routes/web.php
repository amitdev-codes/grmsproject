<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\ApplicationSettingController;
use Modules\Setting\Http\Controllers\ProfileUpdateController;
use Modules\Setting\Http\Controllers\SettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('edit-profile', [ProfileUpdateController::class,'edit'])->name('profile.edit');
    Route::post('update-profile', [ProfileUpdateController::class,'update'])->name('profile.update');

    Route::get('settings/application', [ApplicationSettingController::class, 'edit'])
        ->name('settings.application.edit');
    Route::patch('settings/application', [ApplicationSettingController::class, 'update'])
        ->name('settings.application.update');
});
