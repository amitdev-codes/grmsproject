<?php

use Illuminate\Support\Facades\Route;
use Modules\Grievance\Http\Controllers\GrievanceCategoryController;
use Modules\Grievance\Http\Controllers\GrievanceChannelController;
use Modules\Grievance\Http\Controllers\GrievanceController;
use Modules\Grievance\Http\Controllers\GrievanceEscalationController;
use Modules\Grievance\Http\Controllers\GrievanceMessageController;
use Modules\Grievance\Http\Controllers\GrievanceRoutingController;
use Modules\Grievance\Http\Controllers\GrievanceStatusHistoryController;
use Modules\Grievance\Http\Controllers\PublicGrievanceController;
use Modules\Grievance\Http\Controllers\ResolutionController;

Route::middleware(['auth', 'verified'])->group(function () {
    //grievance categories
    Route::post('/grievance-categories/bulk-destroy', [GrievanceCategoryController::class, 'bulkDestroy'])->name('grievance-categories.bulk-destroy');
    Route::get('/grievance-categories/export', [GrievanceCategoryController::class, 'export'])->name('grievance-categories.export');
    Route::post('/grievance-categories/import', [GrievanceCategoryController::class, 'import'])->name('grievance-categories.import');
    Route::resource('grievance-categories', GrievanceCategoryController::class)->names('grievance-categories');
    //grievance channel
    Route::post('/grievance-channel/bulk-destroy', [GrievanceChannelController::class, 'bulkDestroy'])->name('grievance-channels.bulk-destroy');
    Route::get('/grievance-channel/export', [GrievanceChannelController::class, 'export'])->name('grievance-channels.export');
    Route::post('/grievance-channel/import', [GrievanceChannelController::class, 'import'])->name('grievance-channels.import');
    Route::resource('grievance-channels', GrievanceChannelController::class)->names('grievance-channels');
    //grievance status histories
    Route::post('/grievance-status-histories/bulk-destroy', [GrievanceStatusHistoryController::class, 'bulkDestroy'])->name('grievance-status-histories.bulk-destroy');
    Route::get('/grievance-status-histories/export', [GrievanceStatusHistoryController::class, 'export'])->name('grievance-status-histories.export');
    Route::post('/grievance-status-histories/import', [GrievanceStatusHistoryController::class, 'import'])->name('grievance-status-histories.import');
    Route::resource('grievance-status-histories', GrievanceStatusHistoryController::class)->names('grievance-status-histories');
    //grievance messages
    Route::post('/grievance-messages/bulk-destroy', [GrievanceMessageController::class, 'bulkDestroy'])->name('grievance-messages.bulk-destroy');
    Route::get('/grievance-messages/export', [GrievanceMessageController::class, 'export'])->name('grievance-messages.export');
    Route::post('/grievance-messages/import', [GrievanceMessageController::class, 'import'])->name('grievance-messages.import');
    Route::resource('grievance-messages', GrievanceMessageController::class)->names('grievance-messages');
    //grievance escalations
    Route::post('/grievance-escalations/bulk-destroy', [GrievanceEscalationController::class, 'bulkDestroy'])->name('grievance-escalations.bulk-destroy');
    Route::get('/grievance-escalations/export', [GrievanceEscalationController::class, 'export'])->name('grievance-escalations.export');
    Route::post('/grievance-escalations/import', [GrievanceEscalationController::class, 'import'])->name('grievance-escalations.import');
    Route::resource('grievance-escalations', GrievanceEscalationController::class)->names('grievance-escalations');
    //grievance resolutions
    Route::post('/resolutions/bulk-destroy', [ResolutionController::class, 'bulkDestroy'])->name('resolutions.bulk-destroy');
    Route::get('/resolutions/export', [ResolutionController::class, 'export'])->name('resolutions.export');
    Route::post('/resolutions/import', [ResolutionController::class, 'import'])->name('resolutions.import');
    Route::resource('resolutions', ResolutionController::class)->names('resolutions');
});
//Route::middleware(['auth', 'role:helpdesk_officer|grms_admin|super_admin'])
Route::middleware(['auth'])
    ->prefix('grievances')->name('grievances.')->group(function () {
        Route::get('/', [GrievanceController::class, 'index'])->name('index');
        Route::get('/export', [GrievanceController::class, 'export'])->name('export');
        Route::get('/create', [GrievanceController::class, 'create'])->name('create');
        Route::post('/', [GrievanceController::class, 'store'])->name('store');
        Route::get('/{grievance}/edit', [GrievanceController::class, 'edit'])->name('edit');
        Route::put('/{grievance}', [GrievanceController::class, 'update'])->name('update');
        Route::delete('/bulk', [GrievanceController::class, 'bulkDestroy'])->name('bulk-destroy');
    });
Route::prefix('grievances')->group(function () {
    Route::post('/add', [PublicGrievanceController::class, 'store']);
    Route::get('/track', [PublicGrievanceController::class, 'track']);
    Route::post('/{grievance:reference_no}/messages', [PublicGrievanceController::class, 'storeMessage']);
});
Route::middleware(['auth'])->prefix('grievances')->name('grievances.')->group(function () {
    Route::middleware('role:responsible_manager|super_admin')->group(function () {
        Route::get('/triage', [GrievanceRoutingController::class, 'triageQueue'])->name('triage');
        Route::post('/{grievance}/allocate-division', [GrievanceRoutingController::class, 'allocateDivision'])->name('allocate-division');
    });

    Route::middleware('role:division_director|super_admin')->group(function () {
        Route::get('/division-queue', [GrievanceRoutingController::class, 'divisionQueue'])->name('division-queue');
        Route::post('/{grievance}/allocate-section', [GrievanceRoutingController::class, 'allocateSection'])->name('allocate-section');
    });

    Route::middleware('role:section_manager|super_admin')->group(function () {
        Route::get('/section-queue', [GrievanceRoutingController::class, 'sectionQueue'])->name('section-queue');
        Route::post('/{grievance}/reject-allocation', [GrievanceRoutingController::class, 'rejectAllocation'])->name('reject-allocation');
        Route::post('/{grievance}/assign-officer', [GrievanceRoutingController::class, 'assignOfficer'])->name('assign-officer');
    });
});
