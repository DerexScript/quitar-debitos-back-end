<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\ChargeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->middleware(['cors'])->group(function () {
    Route::post('login', [AuthController::class, 'signin']);
    Route::post('register', [AuthController::class, 'signup']);



    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('charge')->group(function () {
            Route::get('/', [ChargeController::class, 'index']);
            Route::get('/{charge}', [ChargeController::class, 'show']);
            Route::post('store', [ChargeController::class, 'store']);
            Route::get('/installments/{charge}', [ChargeController::class, 'getInstallments']);
        });
    });
});
