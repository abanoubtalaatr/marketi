<?php

namespace App\Http\Controllers\Api;

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
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return $this->success($plans);
    }

    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::where('user_id', $request->user()->id)
            ->with('plan')
            ->latest()
            ->get();

        return $this->success($subscriptions);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate(['subscription_plan_id' => 'required|exists:subscription_plans,id']);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        $subscription = Subscription::create([
            'user_id' => $request->user()->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => $plan->billing_cycle === 'monthly' ? now()->addMonth() : now()->addYear(),
        ]);

        return $this->success($subscription->load('plan'), 'Subscribed successfully', 201);
    }

    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        if ($subscription->user_id !== $request->user()->id) {
            return $this->error('Subscription not found', 404);
        }

        $subscription->update(['status' => 'cancelled']);

        return $this->success($subscription, 'Subscription cancelled');
    }
}
