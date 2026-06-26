<?php

namespace App\Http\Controllers\Api\Admin;

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
        $payments = Payment::with(['user', 'order'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return $this->success($payments);
    }

    public function revenue(): JsonResponse
    {
        $data = [
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'pending_amount' => Payment::where('status', 'pending')->sum('amount'),
            'refunded_amount' => Payment::where('status', 'refunded')->sum('amount'),
        ];

        return $this->success($data);
    }
}
