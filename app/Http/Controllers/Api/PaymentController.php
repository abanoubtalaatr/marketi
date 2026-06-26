<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->with('order')
            ->latest()
            ->paginate(15);

        return $this->success($payments);
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->user_id !== $request->user()->id) {
            return $this->error('Payment not found', 404);
        }

        $payment->load('order.items');

        return $this->success($payment);
    }
}
