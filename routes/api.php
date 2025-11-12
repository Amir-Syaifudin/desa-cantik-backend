<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StatisticTypeController;
use App\Http\Controllers\Api\VillageStatisticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ===============================================
    // PUBLIC AUTHENTICATION ENDPOINTS
    // ===============================================
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('auth/password/reset', [AuthController::class, 'resetPassword']);

    // ===============================================
    // PROTECTED AUTHENTICATION ENDPOINTS
    // ===============================================
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile & Authentication
        Route::get('auth/user', [AuthController::class, 'me']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('auth/password', [AuthController::class, 'updatePassword']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout/all', [AuthController::class, 'logoutAll']);
        Route::post('auth/token/refresh', [AuthController::class, 'refresh']);

        // Village Statistics Management (Protected)
        Route::post('villages/{village}/statistics', [VillageStatisticController::class, 'store']);
        Route::put('villages/{village}/statistics/{statistic}', [VillageStatisticController::class, 'update']);
        Route::delete('villages/{village}/statistics/{statistic}', [VillageStatisticController::class, 'destroy']);
        Route::post('villages/{village}/statistics/import', [VillageStatisticController::class, 'import'])
            ->middleware('throttle:imports');
    });

    // ===============================================
    // PUBLIC DATA ENDPOINTS
    // ===============================================
    Route::get('statistic-types', [StatisticTypeController::class, 'index']);
    Route::get('villages/{village}/statistics', [VillageStatisticController::class, 'index']);
    Route::get('villages/{village}/statistics/summary', [VillageStatisticController::class, 'summary']);
    Route::get('villages/{village}/statistics/export', [VillageStatisticController::class, 'export'])
        ->middleware('throttle:exports');
});

// Legacy endpoint for backward compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});