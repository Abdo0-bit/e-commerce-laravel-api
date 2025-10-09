<?php

use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Client\CartController;
use App\Http\Controllers\Api\Client\CategoryController as ClientCategoryController;
use App\Http\Controllers\Api\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Api\Client\ProductController as ClientProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Public Client Routes (no authentication required)
Route::prefix('client')->group(function () {
    Route::apiResource('categories', ClientCategoryController::class)->only(['index', 'show']);
    Route::apiResource('products', ClientProductController::class)->only(['index', 'show']);
    
    // Cart Routes (available for both guest and authenticated users)
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::apiResource('cart', CartController::class)->except(['create', 'edit']);
});

// Protected Client Routes (authentication required)
Route::middleware('auth:sanctum')->prefix('client')->group(function () {
    // Order Routes (checkout requires authentication)
    Route::apiResource('orders', ClientOrderController::class)->only(['index', 'store', 'show']);
    Route::PATCH('/orders/{id}/cancel', [ClientOrderController::class, 'cancel']);
});

// Admin Routes (authentication + admin role required)
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {
    Route::apiResource('products', AdminProductController::class);
    Route::apiResource('categories', AdminCategoryController::class); 
    Route::apiResource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    
    Route::get('dashboard', DashboardController::class);
});

Route::fallback(function () {
    return response()->json(['message' => 'Route not found.'], 404);
});
