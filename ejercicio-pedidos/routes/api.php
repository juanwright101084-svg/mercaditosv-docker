<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================
// Autenticación (Sanctum)
// ============================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
});

// ============================================
// Rutas públicas (productos, categorías, imágenes)
// ============================================
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Servir imágenes de productos (público)
Route::get('/productos/{filename}', function ($filename) {
    $path = storage_path('app/public/productos/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => mime_content_type($path),
    ]);
})->where('filename', '.*');

// ============================================
// Rutas protegidas (requieren autenticación Sanctum)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    // Productos (admin)
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Categorías (admin)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Órdenes
    Route::apiResource('orders', OrderController::class);

    // Pagos con Stripe
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/my-orders', [PaymentController::class, 'myOrders']);
});