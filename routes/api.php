<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\Seller\OrderController as SellerOrderController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // --- Publik (Orang 2: feature/auth-api) ---
    // POST /register, POST /login (throttle:5,1)

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        // --- Auth & katalog (Orang 2) ---
        // POST /logout, GET /products, GET /products/{product} (catat ProductView), GET /categories

        // --- Pesanan pembeli ---
        Route::post('/orders', [OrderController::class, 'store'])
            ->can('create', Order::class);
        Route::get('/orders/me', [OrderController::class, 'mine']); // sebelum /orders/{order}
        Route::get('/orders/{order}', [OrderController::class, 'show'])
            ->can('view', 'order');

        Route::get('/recommendations', RecommendationController::class);

        // --- Penjual ---
        Route::prefix('seller')->middleware('role:penjual')->group(function () {
            // Produk penjual (Orang 2): GET/POST /products, PATCH /products/{product}, PATCH /products/{product}/stock

            Route::get('/orders', [SellerOrderController::class, 'index']);
            Route::patch('/orders/{order}/status', [SellerOrderController::class, 'updateStatus'])
                ->can('updateStatus', 'order');
        });

        // --- Admin (Orang 2 + policy Orang 3) ---
        // GET/POST /admin/categories, PATCH /admin/products/{product}/deactivate
    });
});
