<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Guest (auth pages)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Web Routes — Authenticated
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/files', [FileController::class, 'index'])->name('files.index');
    Route::get('/files/create', [FileController::class, 'create'])->name('files.create');
    Route::post('/files', [FileController::class, 'store'])->middleware('throttle:30,1')->name('files.store');
    Route::post('/files/bulk', [FileController::class, 'bulk'])->name('files.bulk');
    Route::get('/files/export', [FileController::class, 'exportCsv'])->name('files.export');
    Route::get('/files/trash', [FileController::class, 'trash'])->name('files.trash');
    Route::post('/files/{id}/restore', [FileController::class, 'restore'])->name('files.restore');
    Route::delete('/files/{id}/force', [FileController::class, 'forceDelete'])->name('files.force-delete');
    Route::get('/files/{file}', [FileController::class, 'show'])->name('files.show');
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
});
