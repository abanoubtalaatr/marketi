<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(private PayPalPaymentService $paypal) {}

    public function initiateOnlinePayment(Order $order, Payment $payment): Payment
    {
        $checkout = $this->paypal->createCheckoutOrder(
            $payment,
            "Order {$order->order_number}"
        );

        $payment->update([
            'gateway' => 'paypal',
            'transaction_id' => $checkout['order_id'],
            'payment_url' => $checkout['payment_url'],
            'gateway_response' => $checkout['gateway_response'],
            'status' => PaymentStatus::Pending,
        ]);

        return $payment->fresh();
    }

    public function completePayment(Payment $payment, ?array $gatewayData = null): Payment
    {
        return DB::transaction(function () use ($payment, $gatewayData) {
            $payment->refresh();
            $order = $payment->order;

            if ($payment->status === PaymentStatus::Paid) {
                return $payment;
            }

            $payment->update([
                'status' => PaymentStatus::Paid,
                'gateway_response' => $gatewayData ?? $payment->gateway_response,
            ]);

            $order->update(['status' => OrderStatus::Confirmed]);

            Notification::create([
                'user_id' => $order->user_id,
                'title' => 'Payment Successful',
                'body' => "Your PayPal payment for order {$order->order_number} was successful.",
                'type' => 'order',
                'data' => ['order_id' => $order->id, 'payment_id' => $payment->id],
            ]);

            return $payment->fresh(['order']);
        });
    }

    public function failPayment(Payment $payment, ?array $gatewayData = null): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Failed,
            'gateway_response' => $gatewayData ?? $payment->gateway_response,
        ]);

        return $payment->fresh(['order']);
    }

    public function captureAndComplete(Payment $payment, ?string $paypalOrderId = null): Payment
    {
        $orderId = $paypalOrderId ?? $payment->transaction_id;

        if (! $orderId || str_starts_with($orderId, 'mock_')) {
            return $payment;
        }

        $capture = $this->paypal->captureOrder($orderId);

        if (($capture['status'] ?? '') === 'COMPLETED') {
            return $this->completePayment($payment, $capture);
        }

        return $this->failPayment($payment, $capture);
    }

    public function handlePayPalWebhook(array $payload): void
    {
        $eventType = $payload['event_type'] ?? null;
        $resource = $payload['resource'] ?? [];

        match ($eventType) {
            'CHECKOUT.ORDER.APPROVED' => $this->handleOrderApproved($resource),
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($resource),
            'PAYMENT.CAPTURE.DENIED' => $this->handleCaptureDenied($resource),
            default => Log::info('PayPal webhook received', ['event' => $eventType]),
        };
    }

    private function handleOrderApproved(array $resource): void
    {
        $paypalOrderId = $resource['id'] ?? null;
        if (! $paypalOrderId) {
            return;
        }

        $payment = Payment::where('transaction_id', $paypalOrderId)->first();
        if ($payment && $payment->status === PaymentStatus::Pending) {
            $this->captureAndComplete($payment, $paypalOrderId);
        }
    }

    private function handleCaptureCompleted(array $resource): void
    {
        $payment = $this->findPaymentFromResource($resource);
        if ($payment && $payment->status !== PaymentStatus::Paid) {
            $this->completePayment($payment, $resource);
        }
    }

    private function handleCaptureDenied(array $resource): void
    {
        $payment = $this->findPaymentFromResource($resource);
        if ($payment) {
            $this->failPayment($payment, $resource);
        }
    }

    private function findPaymentFromResource(array $resource): ?Payment
    {
        $customId = $resource['custom_id'] ?? null;
        if ($customId) {
            return Payment::find($customId);
        }

        $referenceId = $resource['invoice_id']
            ?? $resource['reference_id']
            ?? ($resource['supplementary_data']['related_ids']['order_id'] ?? null);

        if ($referenceId) {
            return Payment::where('transaction_id', $referenceId)
                ->orWhere('payment_number', $referenceId)
                ->first();
        }

        return null;
    }

    public function verifyAndComplete(Payment $payment, ?string $paypalToken = null): Payment
    {
        $orderId = $paypalToken ?? $payment->transaction_id;

        if (! $orderId || str_starts_with($orderId, 'mock_')) {
            return $payment;
        }

        if ($paypalToken && $payment->transaction_id !== $paypalToken) {
            $payment->update(['transaction_id' => $paypalToken]);
        }

        return $this->captureAndComplete($payment, $orderId);
    }

    public function retryPayment(Order $order): Payment
    {
        $payment = $order->payment;

        if (! $payment || $payment->status === PaymentStatus::Paid) {
            throw new \RuntimeException('Payment cannot be retried');
        }

        return $this->initiateOnlinePayment($order, $payment);
    }

    public function isMockMode(): bool
    {
        return config('paypal.mock_mode') || ! $this->paypal->isConfigured();
    }
}
