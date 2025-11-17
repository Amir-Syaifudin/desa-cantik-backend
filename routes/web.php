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
