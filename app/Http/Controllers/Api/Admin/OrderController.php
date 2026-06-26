<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['user', 'items', 'payment'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return $this->success($orders);
    }

    public function show(Order $order): JsonResponse
    {
        return $this->success($order->load(['user', 'items.product', 'payment', 'deliverySlot']));
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => OrderStatus::from($request->status)]);

        return $this->success($order, 'Order status updated');
    }

    public function refund(Order $order): JsonResponse
    {
        $payment = $order->payment;

        if (! $payment) {
            return $this->error('No payment found for this order', 422);
        }

        $payment->update(['status' => PaymentStatus::Refunded]);
        $order->update(['status' => OrderStatus::Cancelled]);

        return $this->success($order->fresh(['payment']), 'Order refunded');
    }
}
