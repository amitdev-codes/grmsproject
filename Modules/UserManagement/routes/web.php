<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\Http\Controllers\PermissionController;
use Modules\UserManagement\Http\Controllers\RoleController;
use Modules\UserManagement\Http\Controllers\UserController;


Route::middleware(['auth', 'verified'])->group(function () {
    // Static routes FIRST
    Route::post('/users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');

    // Resource route AFTER
    Route::resource('users', UserController::class)->names('users');

    Route::resource('roles', RoleController::class)->names('roles');
    Route::resource('permissions', PermissionController::class)->names('permissions');
});
