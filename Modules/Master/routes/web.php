<?php

use Illuminate\Support\Facades\Route;
use Modules\Master\Http\Controllers\DistrictController;
use Modules\Master\Http\Controllers\DivisionController;
use Modules\Master\Http\Controllers\LocationController;
use Modules\Master\Http\Controllers\SectionController;
use Modules\Master\Http\Controllers\ProjectTypeController;
use Modules\Master\Http\Controllers\ServiceProviderController;
use Modules\Master\Http\Controllers\ProjectController;
use Modules\Master\Http\Controllers\GrievanceSlaPolicyController;
use Modules\Master\Http\Controllers\GrievanceEscalationRuleController;

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

    // GRMS reference data defined in the Master module migrations.
    Route::resource('project-types', ProjectTypeController::class)->parameters(['project-types' => 'record'])->names('project-types');
    Route::resource('service-providers', ServiceProviderController::class)->parameters(['service-providers' => 'record'])->names('service-providers');
    Route::resource('projects', ProjectController::class)->parameters(['projects' => 'record'])->names('projects');
    Route::resource('grievance-sla-policies', GrievanceSlaPolicyController::class)->parameters(['grievance-sla-policies' => 'record'])->names('grievance-sla-policies');
    Route::resource('grievance-escalation-rules', GrievanceEscalationRuleController::class)->parameters(['grievance-escalation-rules' => 'record'])->names('grievance-escalation-rules');
});
