<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\Api\DistrictLookupController;
use Modules\Frontend\Http\Controllers\Api\DivisionLookupController;
use Modules\Frontend\Http\Controllers\Api\GrievanceCategoryLookupController;
use Modules\Frontend\Http\Controllers\FrontendController;
use Modules\Frontend\Http\Controllers\PublicGrievanceController;

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/faq', [FrontendController::class, 'faq'])->name('faq');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::get('/file-grievance', [FrontendController::class, 'fileGrievance'])->name('file-grievance');
