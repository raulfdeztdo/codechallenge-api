<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('leads')->group(function () {
        Route::get('/list', [LeadController::class, 'index']);
        Route::post('/store', [LeadController::class, 'store']);
        Route::get('/{id}', [LeadController::class, 'show']);
        Route::post('/{id}', [LeadController::class, 'update']);
        Route::delete('/{id}', [LeadController::class, 'destroy']);
    });
});

Route::post('/login', [AuthController::class, 'login']);
