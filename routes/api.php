<?php

use App\Http\Controllers\api\v1\healthCheckController;
use App\Http\Controllers\api\v1\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', healthCheckController::class);
    Route::get('/categories', [CategoryController::class, 'index']);

    // This route requires a valid Bearer Token
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    // Combining with your Spatie Roles
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/category', [CategoryController::class, 'store']);
        Route::put('/category/{category}', [CategoryController::class, 'update']);
        Route::delete('/category/{category}', [CategoryController::class, 'destroy']);
    });

    require __DIR__.'/auth.php';
});
