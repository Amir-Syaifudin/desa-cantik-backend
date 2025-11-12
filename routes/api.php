<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\StatisticTypeController;
use App\Http\Controllers\Api\VillageStatisticController;
use App\Http\Controllers\MapPointsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\VillageProfileController;
use App\Http\Controllers\GeospatialDataController;
use App\Http\Controllers\ThematicMapsController;
use App\Http\Controllers\VillageModuleController;

Route::prefix('v1')->group(function () {
    // ===============================================
    // PUBLIC AUTHENTICATION ENDPOINTS
    // ===============================================
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('auth/password/reset', [AuthController::class, 'resetPassword']);

    // ===============================================
    // PUBLIC DATA ENDPOINTS
    // ===============================================
    Route::get('statistic-types', [StatisticTypeController::class, 'index']);
    Route::get('dashboard/public', [DashboardController::class, 'public']);

    Route::get('villages/{village}/statistics', [VillageStatisticController::class, 'index']);
    Route::get('villages/{village}/statistics/summary', [VillageStatisticController::class, 'summary']);

    // Export with rate limiting for resource-intensive operations
    Route::get('villages/{village}/statistics/export', [VillageStatisticController::class, 'export'])
        ->middleware('throttle:exports');

    Route::get('villages/{village}/publications', [PublicationController::class, 'index']);
    Route::get('publications/{publication}', [PublicationController::class, 'show']);
    Route::get('publications/{publication}/download', [PublicationController::class, 'download'])
        ->name('publications.download');

    // ===============================================
    // PROTECTED ENDPOINTS (Require Authentication)
    // ===============================================
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile & Authentication
        Route::get('auth/user', [AuthController::class, 'me']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('auth/password', [AuthController::class, 'updatePassword']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout/all', [AuthController::class, 'logoutAll']);
        Route::post('auth/token/refresh', [AuthController::class, 'refresh']);

        // Dashboard endpoints with explicit role middleware
        Route::get('dashboard/admin', [DashboardController::class, 'admin'])
            ->middleware('role:bps_admin');

        Route::get('dashboard/village', [DashboardController::class, 'village'])
            ->middleware('role:bps_admin,village_officer');

        // Village Statistics Management (Protected)
        Route::post('villages/{village}/statistics', [VillageStatisticController::class, 'store']);
        Route::put('villages/{village}/statistics/{statistic}', [VillageStatisticController::class, 'update']);
        Route::delete('villages/{village}/statistics/{statistic}', [VillageStatisticController::class, 'destroy']);
        Route::post('villages/{village}/statistics/import', [VillageStatisticController::class, 'import'])
            ->middleware('throttle:imports');

        Route::post('villages/{village}/publications', [PublicationController::class, 'store']);
        Route::put('villages/{village}/publications/{publication}', [PublicationController::class, 'update']);
        Route::post('villages/{village}/publications/{publication}/replace-file', [PublicationController::class, 'replaceFile']);
        Route::delete('villages/{village}/publications/{publication}', [PublicationController::class, 'destroy']);
    });
});

// Legacy endpoint for backward compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ===============================================
// VILLAGE MANAGEMENT ROUTES
// ===============================================
// GET /villages (Get All)
Route::get('/villages', [VillageController::class, 'getAll']);

// GET /villages/{id} (Get Detail)
Route::get('/villages/{id}', [VillageController::class, 'getDetail']);

// POST /villages (Create)
Route::post('/villages', [VillageController::class, 'create']);

// PUT /villages/{id} (Update)
Route::put('/villages/{id}', [VillageController::class, 'update']);

// DELETE /villages/{id} (Delete)
Route::delete('/villages/{id}', [VillageController::class, 'delete']);

// PUT /villages/{id}/toggle-status (Toggle Status Aktif)
Route::put('/villages/{id}/toggle-status', [VillageController::class, 'toggleStatus']);

// ===============================================
// VILLAGE PROFILE ROUTES
// ===============================================
// GET /villages/{id}/profile (Get Profile)
Route::get('/villages/{id}/profile', [VillageProfileController::class, 'getProfile']);

// PUT /villages/{id}/profile (Update Profile)
Route::put('/villages/{id}/profile', [VillageProfileController::class, 'updateProfile']);

// POST /villages/{id}/profile/logo (Upload Logo)
Route::post('/villages/{id}/profile/logo', [VillageProfileController::class, 'uploadLogo']);

// ===============================================
// GEOSPATIAL DATA ROUTES
// ===============================================
// GET /villages/{id}/geospatial (Get Data GeoJSON)
Route::get('/villages/{id}/geospatial', [GeospatialDataController::class, 'getGeoSpatialData']);

// POST /villages/{id}/geospatial (Create Geospatial Data)
Route::post('/villages/{id}/geospatial', [GeospatialDataController::class, 'createGeoSpatialData']);

// PUT /villages/{id}/geospatial/{id} (Update Geospatial Data)
Route::put('/villages/{id}/geospatial/{geoId}', [GeospatialDataController::class, 'updateGeoSpatialData']);

// DELETE /villages/{id}/geospatial/{id} (Delete Geospatial Data)
Route::delete('/villages/{id}/geospatial/{geoId}', [GeospatialDataController::class, 'deleteGeoSpatialData']);

// ===============================================
// THEMATIC MAPS ROUTES
// ===============================================
// GET /villages/{id}/thematic-maps (Get Tema Peta)
Route::get('/villages/{id}/thematic-maps', [ThematicMapsController::class, 'getThematicMaps']);

// GET /thematic-maps/{id} (Detail Tema & Points)
Route::get('/thematic-maps/{id}', [ThematicMapsController::class, 'getThematicMapDetail']);

// POST /villages/{id}/thematic-maps (Create Tema)
Route::post('/villages/{id}/thematic-maps', [ThematicMapsController::class, 'createThematicMap']);

// PUT /villages/{id}/thematic-maps/{id} (Update Tema)
Route::put('/villages/{id}/thematic-maps/{mapId}', [ThematicMapsController::class, 'updateThematicMap']);

// DELETE /villages/{id}/thematic-maps/{id} (Delete Tema)
Route::delete('/villages/{id}/thematic-maps/{mapId}', [ThematicMapsController::class, 'deleteThematicMap']);

// ===============================================
// MAP POINTS ROUTES
// ===============================================
// POST /thematic-maps/{id}/points (Create Titik Peta)
Route::post('/thematic-maps/{id}/points', [MapPointsController::class, 'createMapPoint']);

// PUT /thematic-maps/{id}/points/{pointId} (Update Titik)
Route::put('/thematic-maps/{id}/points/{pointId}', [MapPointsController::class, 'updateMapPoint']);

// DELETE /thematic-maps/{id}/points/{pointId} (Delete Titik)
Route::delete('/thematic-maps/{id}/points/{pointId}', [MapPointsController::class, 'deleteMapPoint']);

// POST /thematic-maps/{id}/points/{pointId}/image (Upload Gambar Titik)
Route::post('/thematic-maps/{id}/points/{pointId}/image', [MapPointsController::class, 'uploadMapPointImage']);

// ===============================================
// VILLAGE MODULES ROUTES
// ===============================================
// GET /villages/{id}/modules (Get Modul Desa)
Route::get('/villages/{id}/modules', [VillageModuleController::class, 'getModules']);

// PUT /villages/{id}/modules/{name}/toggle (Toggle Modul)
Route::put('/villages/{id}/modules/{name}/toggle', [VillageModuleController::class, 'toggleModule']);
