<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Route::inertia('/', '')->name('home');

//Route::get('/forgot-pass', fn () => Inertia::render('auth/forgot-password/index'))->name('forgot-password');
//Route::get('/otp', fn () => Inertia::render('auth/otp/index'))->name('otp');
//Route::get('/401', fn () => Inertia::render('errors/unauthorized-error'))->name('unauthorized-error');
//Route::get('/403', fn () => Inertia::render('errors/forbidden'))->name('forbidden');
//Route::get('/404', fn () => Inertia::render('errors/not-found-error'))->name('not-found-error');
//Route::get('/500', fn () => Inertia::render('errors/general-error'))->name('general-error');
//Route::get('/503', fn () => Inertia::render('errors/maintenance-error'))->name('maintenance-error');
//Route::get('/pricing', fn () => Inertia::render('pricing/index'))->name('pricing');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class,'index'])->name('dashboard');
    Route::get('/lang/{locale}', [LocaleController::class, 'update'])->name('locale.update');
});


require __DIR__.'/settings.php';
