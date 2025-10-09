<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// API Documentation routes
Route::get('/docs', function () {
    $jsonFile = storage_path('api-docs/api-docs.json');
    if (file_exists($jsonFile)) {
        return response()->json(json_decode(file_get_contents($jsonFile), true));
    }
    return response()->json(['error' => 'Documentation not found'], 404);
})->name('l5-swagger.default.docs');

Route::get('/api/docs', function () {
    return redirect('/api/documentation');
});

Route::get('/documentation', function () {
    return redirect('/api/documentation');
});

// Generate API documentation
Route::get('/docs/generate', function () {
    Artisan::call('l5-swagger:generate');
    return response()->json(['message' => 'Documentation generated successfully']);
});
