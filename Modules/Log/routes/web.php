<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\ActivityLogController;
use Modules\Log\Http\Controllers\AuditLogController;
use Modules\Log\Http\Controllers\SystemLogController;

Route::middleware(['auth', 'verified', 'permission:logs.view'])->group(function () {
    Route::get('logs', fn () => redirect()->route('activity-logs.index'))->name('logs.index');
    Route::get('logs/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('logs/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('logs/system-logs', [SystemLogController::class, 'index'])->name('system-logs.index');
});
