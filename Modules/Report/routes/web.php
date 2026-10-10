<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ReportController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('report.index');
    Route::get('/reports/summary', [ReportController::class, 'summary'])
        ->middleware('permission:reports.view')
        ->name('report.summary');
    Route::get('/reports/annex', [ReportController::class, 'annex'])
        ->middleware('permission:reports.view')
        ->name('report.annex');
    Route::get('/reports/detailed', [ReportController::class, 'detailed'])
        ->middleware('permission:reports.view')
        ->name('report.detailed');
});
