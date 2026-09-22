<?php

use App\Http\Controllers\Api\FileApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum)
|--------------------------------------------------------------------------
*/

// Publik: tukar kredensial dengan token
Route::post('/auth/token', [FileApiController::class, 'token'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::get('/files', [FileApiController::class, 'index']);
    Route::post('/files', [FileApiController::class, 'store'])->middleware('throttle:30,1');
    Route::get('/files/{file}', [FileApiController::class, 'show']);
    Route::get('/files/{file}/download', [FileApiController::class, 'download']);
    Route::delete('/files/{file}', [FileApiController::class, 'destroy']);
});
