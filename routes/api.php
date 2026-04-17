<?php

use App\Http\Controllers\api\v1\healthCheckController;
use App\Http\Controllers\api\v1\CategoryController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\CartController;
use App\Http\Controllers\api\v1\CheckoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', healthCheckController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    
    // This route requires a valid Bearer Token

    Route::middleware(['auth:sanctum', 'role:user'])->group(function (){
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        /* cart routes */
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::put('/cart/{id}', [CartController::class, 'update']);
        Route::delete('/cart/{id}', [CartController::class, 'destroy']);

        /* checkout routes */
        // Step 1: Create a Stripe Payment Intent (for Stripe only)
        Route::post('/checkout/create-payment-intent', [CheckoutController::class, 'createPaymentIntent']);
        // Step 2: Finalize the order (for both Stripe and COD)
        Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder']);
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
