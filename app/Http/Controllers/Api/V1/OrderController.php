<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Pesanan dari sisi pembeli (KF-07, KF-08, KF-09). */
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    /** POST /api/v1/orders — checkout keranjang, 1 pesanan per penjual. */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $orders = $this->orders->checkout($request->user(), $request->validated());

        return OrderResource::collection($orders->each->load('seller'))
            ->additional(['message' => 'Pesanan berhasil dibuat'])
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/v1/orders/me — riwayat pesanan pembeli. */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->orders()
            ->with(['seller', 'items'])
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    /** GET /api/v1/orders/{order} — detail; hak akses dicek OrderPolicy::view. */
    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['seller', 'buyer', 'items']));
    }
}
