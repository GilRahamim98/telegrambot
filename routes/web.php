<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::post('/dashboard/update/{type}/{id}', [DashboardController::class, 'update'])->name('dashboard.update');
Route::delete('/dashboard/delete/{type}/{id}', [DashboardController::class, 'destroy'])->name('dashboard.delete');
