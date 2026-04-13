<?php

use App\Http\Controllers\api\v1\healthCheckController;
use App\Http\Controllers\api\v1\CategoryController;
use App\Http\Controllers\api\v1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', healthCheckController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    
    // This route requires a valid Bearer Token
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    // Combining with your Spatie Roles
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

        /*category routes*/
        Route::post('/category', [CategoryController::class, 'store']);
        Route::put('/category/{category}', [CategoryController::class, 'update']);
        Route::get('/category/{category}', [CategoryController::class, 'show']);
        Route::delete('/category/{category}', [CategoryController::class, 'destroy']);

        /*product routes*/
        Route::post('/product', [ProductController::class, 'store']);
        Route::put('/product/{product}', [ProductController::class, 'update']);
        Route::get('/product/{product}', [ProductController::class, 'show']);
        Route::delete('/product/{product}', [ProductController::class, 'destroy']);
    });

    require __DIR__.'/auth.php';
});
