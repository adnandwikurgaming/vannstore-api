<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/** Pesanan masuk dari sisi penjual (KF-09, KF-10, UC-02). */
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    /** GET /api/v1/seller/orders?status=diproses */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['status' => ['nullable', Rule::enum(OrderStatus::class)]]);

        $orders = $request->user()->sellerOrders()
            ->with(['buyer', 'items'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    /** PATCH /api/v1/seller/orders/{order}/status — kepemilikan dicek OrderPolicy::updateStatus. */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $order = $this->orders->updateStatus($order, $request->status());

        return (new OrderResource($order->load('buyer')))
            ->additional(['message' => 'Status pesanan diperbarui']);
    }
}
