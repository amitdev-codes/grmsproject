<?php

use Illuminate\Support\Facades\Route;
use Modules\Grievance\Http\Controllers\GrievanceController;
use Modules\Grievance\Http\Controllers\PublicGrievanceController;
use Modules\Grievance\Http\Controllers\ReferenceDataController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('grievances', GrievanceController::class)->names('grievance');
});

Route::prefix('v1')->group(function () {
    Route::post('/grievances/{grievance:reference_no}/rating', [PublicGrievanceController::class, 'rate']);
    Route::get('/grievance-categories', [ReferenceDataController::class, 'categories']);
    Route::get('/districts', [ReferenceDataController::class, 'districts']);
    Route::get('/divisions', [ReferenceDataController::class, 'divisions']);
});
