<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    use ApiResponse;

    public function __construct(private PaymentService $paymentService) {}

    public function paypalSuccess(Request $request, Payment $payment): JsonResponse
    {
        $paypalToken = $request->query('token');

        $payment = $this->paymentService->verifyAndComplete($payment, $paypalToken);

        return $this->success([
            'payment' => $payment,
            'order' => $payment->order,
        ], $payment->status->value === 'paid' ? 'Payment completed' : 'Payment pending');
    }

    public function paypalCancel(Payment $payment): JsonResponse
    {
        return $this->error('Payment was cancelled by user', 422, [
            'payment' => $payment,
            'order' => $payment->order,
        ]);
    }

    public function paypalWebhook(Request $request): JsonResponse
    {
        $this->paymentService->handlePayPalWebhook($request->all());

        return $this->success(null, 'Webhook processed');
    }

    public function mockCheckout(Payment $payment): JsonResponse
    {
        if (! $this->paymentService->isMockMode()) {
            return $this->error('Mock mode is disabled', 403);
        }

        return $this->success([
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'amount' => $payment->amount,
            'gateway' => 'paypal',
            'message' => 'Mock PayPal checkout. Call POST /api/v1/payments/mock/{id}/complete to simulate payment.',
            'complete_url' => url("/api/v1/payments/mock/{$payment->id}/complete"),
        ]);
    }

    public function mockComplete(Payment $payment): JsonResponse
    {
        if (! $this->paymentService->isMockMode()) {
            return $this->error('Mock mode is disabled', 403);
        }

        $payment = $this->paymentService->completePayment($payment, ['mock' => true, 'status' => 'COMPLETED']);

        return $this->success([
            'payment' => $payment,
            'order' => $payment->order,
        ], 'Mock PayPal payment completed');
    }

    public function retry(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order not found', 404);
        }

        if ($order->payment_type !== 'online') {
            return $this->error('Only online orders support payment retry', 422);
        }

        try {
            $payment = $this->paymentService->retryPayment($order);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success([
            'payment_url' => $payment->payment_url,
            'payment' => $payment,
            'order' => $order->fresh(['payment']),
        ], 'PayPal payment link regenerated');
    }
}
