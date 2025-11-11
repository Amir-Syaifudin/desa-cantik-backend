<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\VillageProfileController;
use App\Http\Controllers\GeospatialDataController;
use App\Http\Controllers\ThematicMapsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

#Village Management
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

#Village Profile
// GET /villages/{id}/profile (Get Profile)
Route::get('/villages/{id}/profile', [VillageProfileController::class, 'getProfile']);

// PUT /villages/{id}/profile (Update Profile)
Route::put('/villages/{id}/profile', [VillageProfileController::class, 'updateProfile']);

// POST /villages/{id}/profile/logo (Upload Logo)
Route::post('/villages/{id}/profile/logo', [VillageProfileController::class, 'uploadLogo']);

#Geospatial Data
// GET /villages/{id}/geospatial (Get Data GeoJSON)
Route::get('/villages/{id}/geospatial', [GeospatialDataController::class, 'getGeoSpatialData']);

// POST /villages/{id}/geospatial (Create Geospatial Data)
Route::post('/villages/{id}/geospatial', [GeospatialDataController::class, 'createGeoSpatialData']);

// PUT /villages/{id}/geospatial/{id} (Update Geospatial Data)
Route::put('/villages/{id}/geospatial/{geoId}', [GeospatialDataController::class, 'updateGeoSpatialData']);

// DELETE /villages/{id}/geospatial/{id} (Delete Geospatial Data)
Route::delete('/villages/{id}/geospatial/{geoId}', [GeospatialDataController::class, 'deleteGeoSpatialData']);

#Thematic Maps
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