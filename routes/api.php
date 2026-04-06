<?php

use App\Http\Controllers\api\healthCheckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', healthCheckController::class);

    // This route requires a valid Bearer Token
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    // Combining with your Spatie Roles
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        
    });

    require __DIR__.'/auth.php';
});
