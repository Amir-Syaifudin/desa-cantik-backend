<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VillageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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