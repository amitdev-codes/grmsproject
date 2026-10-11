<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\FrontendController;
use Modules\Frontend\Http\Controllers\PublicGrievanceController;
use Modules\Grievance\Http\Middleware\ProtectPublicGrievanceLodging;

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/faq', [FrontendController::class, 'faq'])->name('faq');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::get('/file-grievance', [FrontendController::class, 'fileGrievance'])->name('file-grievance');
Route::get('/lodge-by-voice', [FrontendController::class, 'voiceGrievance'])->name('voice-grievance.create');
Route::post('/lodge-by-voice', [PublicGrievanceController::class, 'storeVoice'])->middleware(ProtectPublicGrievanceLodging::class)->name('voice-grievance.store');
Route::get('/voice-grievance/received/{reference}', [FrontendController::class, 'voiceGrievanceReceived'])->name('voice-grievance.received');
