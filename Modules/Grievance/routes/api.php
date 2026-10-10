<?php

use Illuminate\Support\Facades\Route;
use Modules\Grievance\Http\Controllers\GrievanceController;
use Modules\Grievance\Http\Controllers\GrievanceDocumentationController;
use Modules\Grievance\Http\Controllers\MobilePublicApiController;
use Modules\Grievance\Http\Controllers\PublicGrievanceController;
use Modules\Grievance\Http\Controllers\ReferenceDataController;
use Modules\Grievance\Http\Controllers\UssdController;
use Modules\Grievance\Http\Middleware\ProtectPublicGrievanceLodging;

Route::post('/ussd', [UssdController::class, 'handle'])->name('ussd.handle');
Route::get('/grievance-docs', GrievanceDocumentationController::class)->name('grievance.docs');

Route::prefix('v1/mobile')->name('v1.mobile.')->group(function () {
    Route::get('/captcha', [MobilePublicApiController::class, 'captcha'])
        ->middleware('throttle:30,1')
        ->name('captcha');
    Route::get('/landing', [MobilePublicApiController::class, 'landing'])
        ->middleware('throttle:60,1')
        ->name('landing');
    Route::get('/reports/summary', [MobilePublicApiController::class, 'reportSummary'])
        ->middleware('throttle:60,1')
        ->name('reports.summary');
    Route::post('/grievances', [MobilePublicApiController::class, 'lodge'])
        ->middleware(ProtectPublicGrievanceLodging::class)
        ->name('grievances.lodge');
    Route::post('/grievances/track', [MobilePublicApiController::class, 'track'])
        ->middleware('throttle:30,1')
        ->name('grievances.track');
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('grievances', GrievanceController::class)->names('grievance');
});

Route::prefix('v1')->group(function () {
    Route::post('/grievances/{grievance:reference_no}/rating', [PublicGrievanceController::class, 'rate']);
    Route::get('/grievance-categories', [ReferenceDataController::class, 'categories']);
    Route::get('/districts', [ReferenceDataController::class, 'districts']);
    Route::get('/divisions', [ReferenceDataController::class, 'divisions']);
});
