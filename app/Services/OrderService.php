<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private CartService $cartService,
        private PaymentService $paymentService,
    ) {}

    public function placeOrder(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $cart = $this->cartService->getOrCreateCart($user);
            $cart->load('items.product');

            if ($cart->items->isEmpty()) {
                throw new \RuntimeException('Cart is empty');
            }

            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'delivery_address' => $data['delivery_address'],
                'delivery_slot_id' => $data['delivery_slot_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $cart->subtotal,
                'delivery_fee' => $cart->delivery_fee,
                'discount' => $cart->discount,
                'total' => $cart->total,
                'payment_type' => $data['payment_type'],
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                $item->product->decrement('stock_quantity', $item->quantity);
            }

            $payment = Payment::create([
                'payment_number' => 'PAY-'.strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'user_id' => $user->id,
                'amount' => $order->total,
                'status' => PaymentStatus::Pending,
                'payment_method' => $data['payment_type'],
                'gateway' => $data['payment_type'] === 'online' ? 'paypal' : 'cod',
            ]);

            $paymentUrl = null;

            if ($data['payment_type'] === 'online') {
                $payment = $this->paymentService->initiateOnlinePayment($order, $payment);
                $paymentUrl = $payment->payment_url;
            }

            $this->cartService->clear($cart);

            $order = $order->load(['items', 'payment', 'deliverySlot']);

            return [
                'order' => $order,
                'payment_url' => $paymentUrl,
            ];
        });
    }

    public function cancelOrder(Order $order): Order
    {
        if (! in_array($order->status, [OrderStatus::Pending, OrderStatus::Confirmed])) {
            throw new \RuntimeException('Order cannot be cancelled');
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        return $order->fresh();
    }
}
