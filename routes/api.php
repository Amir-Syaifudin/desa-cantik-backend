<?php

use App\Http\Controllers\Api\StatisticTypeController;
use App\Http\Controllers\Api\VillageStatisticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('statistic-types', [StatisticTypeController::class, 'index']);

    Route::get('villages/{village}/statistics', [VillageStatisticController::class, 'index']);
    Route::get('villages/{village}/statistics/summary', [VillageStatisticController::class, 'summary']);
    Route::get('villages/{village}/statistics/export', [VillageStatisticController::class, 'export']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('villages/{village}/statistics', [VillageStatisticController::class, 'store']);
        Route::put('villages/{village}/statistics/{statistic}', [VillageStatisticController::class, 'update']);
        Route::delete('villages/{village}/statistics/{statistic}', [VillageStatisticController::class, 'destroy']);
        Route::post('villages/{village}/statistics/import', [VillageStatisticController::class, 'import']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
