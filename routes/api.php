<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\AuthController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // --- Authentication publik ---
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    // --- Endpoint yang membutuhkan autentikasi ---
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        // Authentication
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // --- Katalog produk ---
        // GET /products, GET /products/{product}, GET /categories

        // --- Pesanan pembeli ---
        Route::post('/orders', [OrderController::class, 'store'])
            ->can('create', Order::class);

        Route::get('/orders/me', [OrderController::class, 'mine']);

        Route::get('/orders/{order}', [OrderController::class, 'show'])
            ->can('view', 'order');

        Route::get('/recommendations', RecommendationController::class);

        // --- Penjual ---
        Route::prefix('seller')
            ->middleware('role:penjual')
            ->group(function () {

                // Produk penjual:
                // GET/POST /products
                // PATCH /products/{product}
                // PATCH /products/{product}/stock

                Route::get('/orders', [SellerOrderController::class, 'index']);

                Route::patch('/orders/{order}/status', [
                    SellerOrderController::class,
                    'updateStatus'
                ])->can('updateStatus', 'order');
            });

        // --- Admin ---
        // GET/POST /admin/categories
        // PATCH /admin/products/{product}/deactivate

    });
});
