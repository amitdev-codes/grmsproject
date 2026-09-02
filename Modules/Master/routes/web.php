<?php

use Illuminate\Support\Facades\Route;
use Modules\Master\Http\Controllers\DistrictController;
use Modules\Master\Http\Controllers\DivisionController;
use Modules\Master\Http\Controllers\LocationController;
use Modules\Master\Http\Controllers\SectionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/locations/divisions', [LocationController::class, 'divisions'])->name('locations.divisions');
    Route::get('/locations/sections', [LocationController::class, 'sections'])->name('locations.sections');

    // Districts
    Route::post('/districts/bulk-destroy', [DistrictController::class, 'bulkDestroy'])->name('districts.bulk-destroy');
    Route::get('/districts/export', [DistrictController::class, 'export'])->name('districts.export');
    Route::post('/districts/import', [DistrictController::class, 'import'])->name('districts.import');
    Route::resource('districts', DistrictController::class)->names('districts');

    // Divisions
    Route::post('/divisions/bulk-destroy', [DivisionController::class, 'bulkDestroy'])->name('divisions.bulk-destroy');
    Route::get('/divisions/export', [DivisionController::class, 'export'])->name('divisions.export');
    Route::post('/divisions/import', [DivisionController::class, 'import'])->name('divisions.import');
    Route::resource('divisions', DivisionController::class)->names('divisions');

    // Sections
    Route::post('/sections/bulk-destroy', [SectionController::class, 'bulkDestroy'])->name('sections.bulk-destroy');
    Route::get('/sections/export', [SectionController::class, 'export'])->name('sections.export');
    Route::post('/sections/import', [SectionController::class, 'import'])->name('sections.import');
    Route::resource('sections', SectionController::class)->names('sections');
});
