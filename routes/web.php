<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'Laravel'),
        'version' => app()->version(),
        'message' => 'Backend service is running.',
    ]);
});

// Swagger UI
Route::get('/api/documentation', function () {
    return view('swagger');
});

// Serve generated OpenAPI artifacts
Route::get('/api/documentation/json', function () {
    $path = storage_path('api-docs/api-docs.json');
    if (! file_exists($path)) {
        abort(404, 'API documentation not generated yet. Run php artisan openapi:generate');
    }

    return response()->file($path, ['Content-Type' => 'application/json']);
});

Route::get('/api/documentation/yaml', function () {
    $path = storage_path('api-docs/api-docs.yaml');
    if (! file_exists($path)) {
        abort(404, 'API documentation not generated yet. Run php artisan openapi:generate');
    }

    return response()->file($path, ['Content-Type' => 'application/yaml']);
});
