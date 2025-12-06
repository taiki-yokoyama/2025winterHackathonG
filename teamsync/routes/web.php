<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnswerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/answer', [AnswerController::class, 'store'])->name('answer.store');
