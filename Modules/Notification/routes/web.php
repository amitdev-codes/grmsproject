<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notification.read');
    Route::post('/notifications/{notification}/unread', [NotificationController::class, 'markUnread'])->name('notification.unread');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notification.read-all');
});
