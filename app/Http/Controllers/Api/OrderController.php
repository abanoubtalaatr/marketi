<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items', 'payment', 'deliverySlot'])
            ->latest()
            ->paginate(15);

        return $this->success($orders);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order not found', 404);
        }

        $order->load(['items.product', 'payment', 'deliverySlot']);

        return $this->success($order);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'delivery_address' => 'required|string',
            'delivery_slot_id' => 'nullable|exists:delivery_slots,id',
            'notes' => 'nullable|string',
            'payment_type' => 'required|in:cash_on_delivery,online',
        ]);

        try {
            $result = $this->orderService->placeOrder($request->user(), $data);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        $message = $data['payment_type'] === 'online'
            ? 'Order placed. Complete payment using the payment_url.'
            : 'Order placed successfully';

        return $this->success($result, $message, 201);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order not found', 404);
        }

        try {
            $order = $this->orderService->cancelOrder($order);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($order, 'Order cancelled');
    }

    public function deliverySlots(): JsonResponse
    {
        $slots = DeliverySlot::where('is_active', true)->get();

        return $this->success($slots);
    }
}
