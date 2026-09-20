<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\ContactMessageController;
use Modules\Setting\Http\Controllers\PublicApplicationSettingController;
use Modules\Setting\Http\Controllers\PublicFaqController;
use Modules\Setting\Http\Controllers\SettingController;

Route::prefix('v1')->group(function () {
    Route::get('application-settings', [PublicApplicationSettingController::class, 'show'])
        ->name('application-settings.show');
    Route::get('faqs', [PublicFaqController::class, 'index'])
        ->name('faqs.index');
    Route::post('contact', [ContactMessageController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('contact.store');
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('settings', SettingController::class)->names('setting');
});
