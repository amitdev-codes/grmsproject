<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\FrontendController;
use Modules\Frontend\Http\Controllers\PublicAnalyticsController;

Route::prefix('v1')->group(function () {
    Route::get('analytics/summary', [PublicAnalyticsController::class, 'summary'])
        ->name('analytics.summary');
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('frontends', FrontendController::class)->names('frontend');
});
