<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use ApiResponse;

    public function plans(): JsonResponse
    {
        return $this->success(SubscriptionPlan::withCount('subscriptions')->get());
    }

    public function storePlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'is_active' => 'boolean',
        ]);

        $plan = SubscriptionPlan::create($data);

        return $this->success($plan, 'Plan created', 201);
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'billing_cycle' => 'sometimes|in:monthly,yearly',
            'is_active' => 'boolean',
        ]);

        $plan->update($data);

        return $this->success($plan, 'Plan updated');
    }

    public function subscribers(): JsonResponse
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->latest()
            ->paginate(20);

        return $this->success($subscriptions);
    }
}
